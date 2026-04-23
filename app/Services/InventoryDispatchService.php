<?php

namespace App\Services;

use App\Models\InventoryDispatch;
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
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                ]);

                $totalCost += $item['cost_price'] * $item['quantity'];
                $expectedSales += $item['selling_price'] * $item['quantity'];

                // Deduct from branch stock
                DB::table('branch_product')
                    ->where('branch_id', $data['branch_id'])
                    ->where('product_id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);
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

    public function settleDispatch(int $id, float $actualSales): InventoryDispatch
    {
        $dispatch = $this->dispatchRepository->getById($id);
        $dispatch->update([
            'actual_sales' => $actualSales,
            'status' => 'settled',
        ]);
        return $dispatch;
    }

    public function deleteDispatch(int $id): bool
    {
        return $this->dispatchRepository->delete($id);
    }
}
