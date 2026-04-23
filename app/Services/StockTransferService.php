<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\Unit;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(protected StockTransferRepositoryInterface $stockTransferRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->stockTransferRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId)
    {
        return $this->stockTransferRepository->paginateWithFilters($perPage, $search, $status, $branchId);
    }

    public function createTransfer(array $data, array $items): StockTransfer
    {
        return DB::transaction(function () use ($data, $items) {
            $transfer = $this->stockTransferRepository->create($data);

            foreach ($items as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_id'    => $item['unit_id'] ?: null,
                ]);
            }

            return $transfer->load('items');
        });
    }

    public function approveTransfer(int $id, int $adminId): StockTransfer
    {
        return DB::transaction(function () use ($id, $adminId) {
            $transfer = $this->stockTransferRepository->getById($id);

            if ($transfer->status !== 'pending') {
                throw new \Exception('لا يمكن الموافقة على هذا التحويل');
            }

            // Deduct from source branch
            foreach ($transfer->items as $item) {
                $branchProduct = DB::table('branch_product')
                    ->where('branch_id', $transfer->from_branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                // Resolve quantity in stock unit
                $deductQty = $this->resolveQtyInStockUnit($item, $branchProduct);

                if (!$branchProduct || $branchProduct->quantity < $deductQty) {
                    throw new \Exception("الكمية غير كافية للمنتج: {$item->product->name}");
                }

                DB::table('branch_product')
                    ->where('branch_id', $transfer->from_branch_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('quantity', $deductQty);
            }

            $transfer->update([
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            return $transfer;
        });
    }

    public function rejectTransfer(int $id, int $adminId): StockTransfer
    {
        $transfer = $this->stockTransferRepository->getById($id);

        if ($transfer->status !== 'pending') {
            throw new \Exception('لا يمكن رفض هذا التحويل');
        }

        $transfer->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $transfer;
    }

    public function receiveTransfer(int $id, int $adminId): StockTransfer
    {
        return DB::transaction(function () use ($id, $adminId) {
            $transfer = $this->stockTransferRepository->getById($id);

            if ($transfer->status !== 'approved') {
                throw new \Exception('لا يمكن استلام هذا التحويل');
            }

            // Add to destination branch
            foreach ($transfer->items as $item) {
                $stockRow = DB::table('branch_product')
                    ->where('branch_id', $transfer->to_branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $addQty    = $this->resolveQtyInStockUnit($item, $stockRow);
                $unitToUse = $stockRow?->unit_id ?? $item->unit_id;

                if ($stockRow) {
                    DB::table('branch_product')
                        ->where('id', $stockRow->id)
                        ->update(['quantity' => $stockRow->quantity + $addQty, 'updated_at' => now()]);
                } else {
                    DB::table('branch_product')->insert([
                        'branch_id'  => $transfer->to_branch_id,
                        'product_id' => $item->product_id,
                        'quantity'   => $addQty,
                        'unit_id'    => $unitToUse,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => $adminId,
                'received_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Convert item quantity to the stock unit quantity.
     * If the result is fractional, convert the stock to base unit first.
     */
    protected function resolveQtyInStockUnit(object $item, ?object $stockRow): int
    {
        $transferUnit = $item->unit_id ? Unit::find($item->unit_id) : null;
        $stockUnit    = $stockRow?->unit_id ? Unit::find($stockRow->unit_id) : null;

        // No unit info — use quantity as-is
        if (!$transferUnit || !$stockUnit) {
            return (int) $item->quantity;
        }

        $transferFactor = (float) $transferUnit->conversion_factor;
        $stockFactor    = (float) $stockUnit->conversion_factor;

        if ($stockFactor <= 0) return (int) $item->quantity;

        $qtyInStock = ((float) $item->quantity * $transferFactor) / $stockFactor;

        if (abs($qtyInStock - round($qtyInStock)) < 0.000001) {
            return (int) round($qtyInStock);
        }

        // Fractional — convert to base unit
        $baseUnitId = $this->resolveBaseUnitId($stockUnit);
        $baseUnit   = Unit::find($baseUnitId);
        if (!$baseUnit) return (int) $item->quantity;

        $qtyInBase = (float) $item->quantity * $transferFactor;
        return (int) round($qtyInBase);
    }

    protected function resolveBaseUnitId(Unit $unit): int
    {
        $current = $unit;
        $hops = 0;
        while ($current->base_unit_id && $hops < 10) {
            $parent = Unit::find($current->base_unit_id);
            if (!$parent) break;
            $current = $parent;
            $hops++;
        }
        return (int) $current->id;
    }
}
