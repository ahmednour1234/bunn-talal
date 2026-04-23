<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\Unit;
use App\Repositories\Contracts\PurchaseReturnRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(protected PurchaseReturnRepositoryInterface $returnRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->returnRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId)
    {
        return $this->returnRepository->paginateWithFilters($perPage, $search, $status, $supplierId);
    }

    public function getFilteredReturns(?string $search, ?string $status, ?int $supplierId)
    {
        return $this->buildFilteredQuery($search, $status, $supplierId)
            ->with(['invoice', 'supplier', 'branch', 'admin'])
            ->latest()
            ->get();
    }

    public function getSummaryStats(?string $search, ?string $status, ?int $supplierId): array
    {
        $baseQuery = $this->buildFilteredQuery($search, $status, $supplierId);

        $totals = (clone $baseQuery)->selectRaw('COUNT(*) as count, COALESCE(SUM(subtotal),0) as subtotal, COALESCE(SUM(loss_amount),0) as loss, COALESCE(SUM(refund_amount),0) as refund')->first();

        $statusCounts = (clone $this->buildFilteredQuery($search, null, $supplierId))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'count' => (int) ($totals->count ?? 0),
            'subtotal' => (float) ($totals->subtotal ?? 0),
            'loss' => (float) ($totals->loss ?? 0),
            'refund' => (float) ($totals->refund ?? 0),
            'status_counts' => [
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'confirmed' => (int) ($statusCounts['confirmed'] ?? 0),
                'refunded' => (int) ($statusCounts['refunded'] ?? 0),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            ],
        ];
    }

    protected function buildFilteredQuery(?string $search, ?string $status, ?int $supplierId)
    {
        $query = PurchaseReturn::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('invoice', fn($i) => $i->where('invoice_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query;
    }

    public function createReturn(array $data, array $items): PurchaseReturn
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            $totalLoss = 0;

            foreach ($items as &$item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                $totalLoss += (float) ($item['loss_amount'] ?? 0);
            }

            $refundAmount = $subtotal - $totalLoss;

            $return = $this->returnRepository->create(array_merge($data, [
                'subtotal' => round($subtotal, 2),
                'loss_amount' => round($totalLoss, 2),
                'refund_amount' => round(max($refundAmount, 0), 2),
                'status' => 'pending',
            ]));

            foreach ($items as $item) {
                $return->items()->create([
                    'purchase_invoice_item_id' => $item['purchase_invoice_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'loss_amount' => $item['loss_amount'] ?? 0,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            return $return->load(['items.product', 'items.unit', 'invoice', 'supplier']);
        });
    }

    public function confirmReturn(int $id): PurchaseReturn
    {
        return DB::transaction(function () use ($id) {
            $return = $this->returnRepository->getById($id);

            if ($return->status !== 'pending') {
                throw new \Exception('لا يمكن تأكيد هذا المرتجع');
            }

            // Deduct stock from branch
            foreach ($return->items as $item) {
                $current = DB::table('branch_product')
                    ->where('branch_id', $return->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$current) {
                    continue;
                }

                $returnUnit = $item->unit_id ? Unit::find($item->unit_id) : null;
                $stockUnit = $current->unit_id ? Unit::find($current->unit_id) : null;

                if (!$returnUnit || !$stockUnit) {
                    if ($current->quantity >= $item->quantity) {
                        DB::table('branch_product')
                            ->where('branch_id', $return->branch_id)
                            ->where('product_id', $item->product_id)
                            ->decrement('quantity', $item->quantity);
                    }
                    continue;
                }

                $qtyInStockUnit = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $stockUnit->conversion_factor;

                if (abs($qtyInStockUnit - round($qtyInStockUnit)) < 0.000001) {
                    $deductQty = (int) round($qtyInStockUnit);
                    if ((int) $current->quantity >= $deductQty) {
                        DB::table('branch_product')
                            ->where('branch_id', $return->branch_id)
                            ->where('product_id', $item->product_id)
                            ->decrement('quantity', $deductQty);
                    }
                    continue;
                }

                $baseUnitId = $this->resolveBaseUnitId($stockUnit);
                $baseUnit = Unit::find($baseUnitId);
                if (!$baseUnit) {
                    continue;
                }

                $currentQtyInBase = ((float) $current->quantity * (float) $stockUnit->conversion_factor) / (float) $baseUnit->conversion_factor;
                $returnQtyInBase = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $baseUnit->conversion_factor;

                $currentQtyInBaseInt = (int) round($currentQtyInBase);
                $returnQtyInBaseInt = (int) round($returnQtyInBase);

                if ($currentQtyInBaseInt < $returnQtyInBaseInt) {
                    continue;
                }

                DB::table('branch_product')
                    ->where('id', $current->id)
                    ->update([
                        'unit_id' => $baseUnit->id,
                        'quantity' => $currentQtyInBaseInt - $returnQtyInBaseInt,
                        'updated_at' => now(),
                    ]);
            }

            // Reduce supplier balance by refund amount
            Supplier::where('id', $return->supplier_id)->decrement('balance', $return->refund_amount);

            // If treasury specified, deposit refund
            if ($return->treasury_id && $return->refund_amount > 0) {
                Treasury::where('id', $return->treasury_id)->increment('balance', $return->refund_amount);
                $return->update(['status' => 'refunded']);
            } else {
                $return->update(['status' => 'confirmed']);
            }

            return $return;
        });
    }

    public function cancelReturn(int $id): PurchaseReturn
    {
        $return = $this->returnRepository->getById($id);

        if ($return->status !== 'pending') {
            throw new \Exception('لا يمكن إلغاء هذا المرتجع');
        }

        $return->update(['status' => 'cancelled']);
        return $return;
    }

    protected function resolveBaseUnitId(Unit $unit): int
    {
        $current = $unit;
        while ($current->base_unit_id) {
            $parent = Unit::find($current->base_unit_id);
            if (!$parent) {
                break;
            }
            $current = $parent;
        }

        return (int) $current->id;
    }
}
