<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SaleReturn;
use App\Models\Treasury;
use App\Models\Unit;
use App\Repositories\Contracts\SaleReturnRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SaleReturnService
{
    public function __construct(protected SaleReturnRepositoryInterface $returnRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->returnRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId = null)
    {
        return $this->returnRepository->paginateWithFilters($perPage, $search, $status, $customerId, $branchId);
    }

    public function getSummaryStats(?string $search, ?string $status, ?int $customerId): array
    {
        $baseQuery = $this->buildFilteredQuery($search, $status, $customerId);

        $totals = (clone $baseQuery)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(subtotal),0) as subtotal, COALESCE(SUM(refund_amount),0) as refund')
            ->first();

        $statusCounts = (clone $this->buildFilteredQuery($search, null, $customerId))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'count'   => (int) ($totals->count ?? 0),
            'subtotal' => (float) ($totals->subtotal ?? 0),
            'refund'  => (float) ($totals->refund ?? 0),
            'status_counts' => [
                'pending'   => (int) ($statusCounts['pending'] ?? 0),
                'confirmed' => (int) ($statusCounts['confirmed'] ?? 0),
                'refunded'  => (int) ($statusCounts['refunded'] ?? 0),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            ],
        ];
    }

    protected function buildFilteredQuery(?string $search, ?string $status, ?int $customerId)
    {
        $query = SaleReturn::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('order', fn($o) => $o->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query;
    }

    public function createReturn(array $data, array $items): SaleReturn
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;

            foreach ($items as &$item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $subtotal += $lineTotal;
                $item['refund_amount'] = round($lineTotal, 2);
            }

            $return = $this->returnRepository->create(array_merge($data, [
                'subtotal'      => round($subtotal, 2),
                'refund_amount' => round($subtotal, 2),
                'status'        => 'pending',
            ]));

            foreach ($items as $item) {
                $return->items()->create([
                    'sale_order_item_id' => $item['sale_order_item_id'] ?? null,
                    'product_id'         => $item['product_id'],
                    'unit_id'            => $item['unit_id'] ?? null,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'refund_amount'      => $item['refund_amount'],
                    'reason'             => $item['reason'] ?? null,
                ]);
            }

            return $return->load(['items.product', 'items.unit', 'order', 'customer']);
        });
    }

    public function confirmReturn(int $id): SaleReturn
    {
        return DB::transaction(function () use ($id) {
            $return = $this->returnRepository->getById($id);

            if ($return->status !== 'pending') {
                throw new \Exception('لا يمكن تأكيد هذا المرتجع');
            }

            // Add stock back to branch
            foreach ($return->items as $item) {
                $current = DB::table('branch_product')
                    ->where('branch_id', $return->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $returnUnit = $item->unit_id ? Unit::find($item->unit_id) : null;

                if (!$returnUnit || !$current) {
                    // Simple increment
                    if ($current) {
                        DB::table('branch_product')
                            ->where('branch_id', $return->branch_id)
                            ->where('product_id', $item->product_id)
                            ->increment('quantity', (float) $item->quantity);
                    } else {
                        DB::table('branch_product')->insert([
                            'branch_id'  => $return->branch_id,
                            'product_id' => $item->product_id,
                            'quantity'   => (float) $item->quantity,
                            'unit_id'    => $item->unit_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    continue;
                }

                $stockUnit = $current->unit_id ? Unit::find($current->unit_id) : null;

                if (!$stockUnit) {
                    DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', (float) $item->quantity);
                    continue;
                }

                // Convert returned qty to stock unit
                $qtyInStockUnit = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $stockUnit->conversion_factor;

                if (abs($qtyInStockUnit - round($qtyInStockUnit)) < 0.000001) {
                    DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', (int) round($qtyInStockUnit));
                    continue;
                }

                // Convert everything to base unit
                $baseUnitId = $this->resolveBaseUnitId($stockUnit);
                $baseUnit = Unit::find($baseUnitId);
                if (!$baseUnit) {
                    continue;
                }

                $currentQtyInBase = (int) round(((float) $current->quantity * (float) $stockUnit->conversion_factor) / (float) $baseUnit->conversion_factor);
                $returnQtyInBase  = (int) round(((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $baseUnit->conversion_factor);

                DB::table('branch_product')
                    ->where('id', $current->id)
                    ->update([
                        'unit_id'    => $baseUnit->id,
                        'quantity'   => $currentQtyInBase + $returnQtyInBase,
                        'updated_at' => now(),
                    ]);
            }

            // Reduce customer balance (they returned goods, so they owe less)
            Customer::where('id', $return->customer_id)->decrement('balance', (float) $return->refund_amount);

            // Deposit refund to treasury if specified
            if ($return->treasury_id && (float) $return->refund_amount > 0) {
                Treasury::where('id', $return->treasury_id)->decrement('balance', (float) $return->refund_amount);
                $return->update(['status' => 'refunded']);
            } else {
                $return->update(['status' => 'confirmed']);
            }

            return $return;
        });
    }

    public function cancelReturn(int $id): SaleReturn
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
