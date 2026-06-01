<?php

namespace App\Services;

use App\Models\InventoryDispatch;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Models\Trip;
use App\Repositories\Contracts\InventoryDispatchRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InventoryDispatchService
{
    public function __construct(protected InventoryDispatchRepositoryInterface $dispatchRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->dispatchRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId, ?int $delegateId)
    {
        return $this->dispatchRepository->paginateWithFilters($perPage, $search, $status, $branchId, $delegateId);
    }

    public function createDispatch(array $data, array $items): InventoryDispatch
    {
        return DB::transaction(function () use ($data, $items) {
            $totalCost = 0;
            $expectedSales = 0;

            // Validate branch stock before proceeding
            foreach ($items as $item) {
                $stock = DB::table('branch_product')
                    ->where('branch_id', $data['branch_id'])
                    ->where('product_id', $item['product_id'])
                    ->value('quantity');

                $available = (float) ($stock ?? 0);
                $requested = (float) $item['quantity'];

                if ($available < $requested) {
                    $product = \App\Models\Product::find($item['product_id']);
                    $name = $product?->name ?? "المنتج #{$item['product_id']}";
                    throw new \Exception("الكمية المطلوبة ({$requested}) من \"{$name}\" تتجاوز مخزون الفرع المتاح ({$available})");
                }
            }

            // Auto-link or auto-create trip for this delegate
            if (!empty($data['delegate_id']) && empty($data['trip_id'])) {
                $trip = Trip::where('delegate_id', $data['delegate_id'])
                    ->whereIn('status', ['draft', 'active'])
                    ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                    ->orderByDesc('id')
                    ->first();

                if (!$trip) {
                    $trip = Trip::create([
                        'delegate_id' => $data['delegate_id'],
                        'branch_id'   => $data['branch_id'],
                        'admin_id'    => $data['admin_id'] ?? null,
                        'status'      => 'active',
                        'start_date'  => $data['date'] ?? now()->toDateString(),
                    ]);
                }

                $data['trip_id'] = $trip->id;
            }

            $dispatch = $this->dispatchRepository->create(array_merge($data, [
                'total_cost' => 0,
                'expected_sales' => 0,
            ]));


            foreach ($items as $item) {
                $dispatch->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'] ?? null,
                    'quantity'      => $item['quantity'],
                    'cost_price'    => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                ]);

                $totalCost += $item['cost_price'] * $item['quantity'];
                $expectedSales += $item['selling_price'] * $item['quantity'];

                // Deduct from branch stock
                DB::table('branch_product')
                    ->where('branch_id', $data['branch_id'])
                    ->where('product_id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);

                // Add to delegate stock
                if (!empty($data['delegate_id'])) {
                    DB::table('delegate_product')->updateOrInsert(
                        ['delegate_id' => $data['delegate_id'], 'product_id' => $item['product_id']],
                        [
                            'quantity'   => DB::raw('COALESCE(quantity, 0) + ' . (float) $item['quantity']),
                            'unit_id'    => $item['unit_id'] ?? null,
                            'updated_at' => now(),
                            'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                        ]
                    );
                }
            }

            $dispatch->update([
                'total_cost' => $totalCost,
                'expected_sales' => $expectedSales,
                'status' => 'dispatched',
            ]);

            return $dispatch->load('items');
        });
    }

    public function returnItems(int $id, array $returnedItems): InventoryDispatch
    {
        return DB::transaction(function () use ($id, $returnedItems) {
            $dispatch = $this->dispatchRepository->getById($id);

            $hasReturn = false;
            $allReturned = true;

            foreach ($returnedItems as $itemId => $returnedQty) {
                if ($returnedQty > 0) {
                    $hasReturn = true;
                    $dispatchItem = $dispatch->items()->findOrFail($itemId);
                    $dispatchItem->update(['returned_quantity' => $returnedQty]);

                    // Return to branch stock
                    DB::table('branch_product')->updateOrInsert(
                        [
                            'branch_id' => $dispatch->branch_id,
                            'product_id' => $dispatchItem->product_id,
                        ],
                        [
                            'quantity' => DB::raw("COALESCE(quantity, 0) + {$returnedQty}"),
                            'updated_at' => now(),
                            'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                        ]
                    );

                    // Deduct from delegate stock
                    if ($dispatch->delegate_id) {
                        DB::table('delegate_product')
                            ->where('delegate_id', $dispatch->delegate_id)
                            ->where('product_id', $dispatchItem->product_id)
                            ->decrement('quantity', (float) $returnedQty);
                    }
                }
            }

            foreach ($dispatch->items as $item) {
                if ($item->returned_quantity < $item->quantity) {
                    $allReturned = false;
                    break;
                }
            }

            $dispatch->update([
                'status' => $allReturned ? 'returned' : ($hasReturn ? 'partial_return' : 'dispatched'),
            ]);

            return $dispatch;
        });
    }

    public function settleDispatch(int $id, float $actualSales, ?int $treasuryId = null, ?int $adminId = null): InventoryDispatch
    {
        return DB::transaction(function () use ($id, $actualSales, $treasuryId, $adminId) {
            $dispatch = $this->dispatchRepository->getById($id);

            // ── 1. Reverse previous settlement if already settled ──────────────
            if ($dispatch->status === 'settled') {
                // Reverse treasury transaction
                if ($dispatch->treasury_transaction_id) {
                    $oldTx = TreasuryTransaction::find($dispatch->treasury_transaction_id);
                    if ($oldTx) {
                        $treasury = Treasury::find($oldTx->treasury_id);
                        if ($treasury) {
                            // Previous settlement deposited actual_sales → reverse with withdrawal
                            $treasury->decrement('balance', $oldTx->amount);
                        }
                        $oldTx->delete();
                    }
                    $dispatch->update(['treasury_transaction_id' => null]);
                }

                // Reverse quantity movements: branch → back to delegate (undo previous settle)
                foreach ($dispatch->items as $item) {
                    $remaining = $item->quantity - ($item->returned_quantity ?? 0);
                    if ($remaining <= 0) continue;

                    // Remove from branch what was added during previous settlement
                    DB::table('branch_product')
                        ->where('branch_id', $dispatch->branch_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('quantity', $remaining);

                    // Restore delegate stock
                    if ($dispatch->delegate_id) {
                        DB::table('delegate_product')->updateOrInsert(
                            ['delegate_id' => $dispatch->delegate_id, 'product_id' => $item->product_id],
                            [
                                'quantity'   => DB::raw('COALESCE(quantity, 0) + ' . (float) $remaining),
                                'unit_id'    => $item->unit_id ?? null,
                                'updated_at' => now(),
                                'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                            ]
                        );
                    }
                }
            }

            // ── 2. Move remaining stock: delegate → branch (zero the delegate) ─
            foreach ($dispatch->items as $item) {
                $remaining = $item->quantity - ($item->returned_quantity ?? 0);
                if ($remaining <= 0) continue;

                // Add remaining back to branch
                DB::table('branch_product')->updateOrInsert(
                    ['branch_id' => $dispatch->branch_id, 'product_id' => $item->product_id],
                    [
                        'quantity'   => DB::raw('COALESCE(quantity, 0) + ' . (float) $remaining),
                        'updated_at' => now(),
                        'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                    ]
                );

                // Zero the delegate for this item
                if ($dispatch->delegate_id) {
                    DB::table('delegate_product')
                        ->where('delegate_id', $dispatch->delegate_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('quantity', (float) $remaining);
                }
            }

            // ── 3. Treasury transaction for actual sales ───────────────────────
            $treasuryTxId = null;
            if ($treasuryId && $actualSales > 0) {
                $treasuryTx = TreasuryTransaction::create([
                    'treasury_id'      => $treasuryId,
                    'type'             => 'deposit',
                    'amount'           => $actualSales,
                    'description'      => 'تسوية أمر الصرف #' . $dispatch->id,
                    'date'             => now()->toDateString(),
                    'reference_number' => 'INV-SETTLE-' . $dispatch->id,
                    'admin_id'         => $adminId,
                ]);

                Treasury::findOrFail($treasuryId)->increment('balance', $actualSales);
                $treasuryTxId = $treasuryTx->id;
            }

            // ── 4. Finalize ────────────────────────────────────────────────────
            $dispatch->update([
                'actual_sales'           => $actualSales,
                'status'                 => 'settled',
                'treasury_transaction_id' => $treasuryTxId,
            ]);

            return $dispatch->load('items');
        });
    }

    public function deleteDispatch(int $id): bool
    {
        return $this->dispatchRepository->delete($id);
    }
}
