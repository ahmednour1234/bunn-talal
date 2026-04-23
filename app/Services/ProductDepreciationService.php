<?php

namespace App\Services;

use App\Models\ProductDepreciation;
use App\Models\Unit;
use App\Repositories\Contracts\ProductDepreciationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductDepreciationService
{
    public function __construct(protected ProductDepreciationRepositoryInterface $depreciationRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->depreciationRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId)
    {
        return $this->depreciationRepository->paginateWithFilters($perPage, $search, $status, $branchId);
    }

    public function createDepreciation(array $data, array $items): ProductDepreciation
    {
        return DB::transaction(function () use ($data, $items) {
            $totalLoss = 0;

            foreach ($items as $item) {
                $totalLoss += $item['quantity'] * $item['cost_price'];
            }

            $depreciation = $this->depreciationRepository->create(array_merge($data, [
                'total_loss' => round($totalLoss, 2),
                'status' => 'pending',
            ]));

            foreach ($items as $item) {
                $depreciation->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'cost_price' => $item['cost_price'],
                    'total_loss' => round($item['quantity'] * $item['cost_price'], 2),
                ]);
            }

            return $depreciation->load(['items.product', 'items.unit', 'branch']);
        });
    }

    public function approveDepreciation(int $id, int $adminId): ProductDepreciation
    {
        return DB::transaction(function () use ($id, $adminId) {
            $depreciation = $this->depreciationRepository->getById($id);

            if ($depreciation->status !== 'pending') {
                throw new \Exception('لا يمكن الموافقة على هذا الطلب');
            }

            // Deduct stock from branch
            foreach ($depreciation->items as $item) {
                $branchProduct = DB::table('branch_product')
                    ->where('branch_id', $depreciation->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $deductQty = $this->resolveQtyInStockUnit($item, $branchProduct);

                if (!$branchProduct || $branchProduct->quantity < $deductQty) {
                    throw new \Exception("الكمية غير كافية للمنتج: {$item->product->name}");
                }

                DB::table('branch_product')
                    ->where('branch_id', $depreciation->branch_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('quantity', $deductQty);
            }

            $depreciation->update([
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            return $depreciation;
        });
    }

    public function rejectDepreciation(int $id, int $adminId): ProductDepreciation
    {
        $depreciation = $this->depreciationRepository->getById($id);

        if ($depreciation->status !== 'pending') {
            throw new \Exception('لا يمكن رفض هذا الطلب');
        }

        $depreciation->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $depreciation;
    }

    protected function resolveQtyInStockUnit(object $item, ?object $stockRow): int
    {
        $depUnit   = $item->unit_id ? Unit::find($item->unit_id) : null;
        $stockUnit = $stockRow?->unit_id ? Unit::find($stockRow->unit_id) : null;

        if (!$depUnit || !$stockUnit) return (int) $item->quantity;

        $depFactor   = (float) $depUnit->conversion_factor;
        $stockFactor = (float) $stockUnit->conversion_factor;

        if ($stockFactor <= 0) return (int) $item->quantity;

        $qty = ((float) $item->quantity * $depFactor) / $stockFactor;

        if (abs($qty - round($qty)) < 0.000001) return (int) round($qty);

        // Fractional — convert to base unit
        return (int) round((float) $item->quantity * $depFactor);
    }
}
