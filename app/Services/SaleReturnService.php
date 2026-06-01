<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SaleReturn;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
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

    public function getDelegateTripReturns(int $tripId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->returnRepository->getDelegateTripReturns($tripId);
    }

    public function getDelegateReturns(int $delegateId, ?int $tripId = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->returnRepository->getDelegateReturns($delegateId, $tripId);
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
            'count' => (int) ($totals->count ?? 0),
            'subtotal' => (float) ($totals->subtotal ?? 0),
            'refund' => (float) ($totals->refund ?? 0),
            'status_counts' => [
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'confirmed' => (int) ($statusCounts['confirmed'] ?? 0),
                'refunded' => (int) ($statusCounts['refunded'] ?? 0),
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
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$search}%"));
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
                $quantity = (float) ($item['quantity'] ?? 0);

                /*
                 * مهم:
                 * unit_price هنا لازم يكون السعر بعد توزيع خصم الفاتورة
                 * وده إحنا عملناه في Livewire SaleReturnForm.
                 */
                $unitPriceAfterDiscount = (float) ($item['unit_price'] ?? 0);

                $refundAmount = round($quantity * $unitPriceAfterDiscount, 2);

                $item['quantity'] = $quantity;
                $item['unit_price'] = round($unitPriceAfterDiscount, 6);
                $item['refund_amount'] = $refundAmount;

                $subtotal += $refundAmount;
            }

            unset($item);

            $return = $this->returnRepository->create(array_merge($data, [
                'subtotal' => round($subtotal, 2),
                'refund_amount' => round($subtotal, 2),
                'status' => 'pending',
            ]));

            foreach ($items as $item) {
                $return->items()->create([
                    'sale_order_item_id' => $item['sale_order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'refund_amount' => $item['refund_amount'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            if (!empty($data['trip_id'])) {
                $trip = \App\Models\Trip::find($data['trip_id']);

                if ($trip && $trip->delegate_id) {
                    foreach ($items as $item) {
                        DB::table('delegate_product')->updateOrInsert(
                            [
                                'delegate_id' => $trip->delegate_id,
                                'product_id' => $item['product_id'],
                            ],
                            [
                                'quantity' => DB::raw('COALESCE(quantity, 0) + ' . (float) $item['quantity']),
                                'unit_id' => $item['unit_id'] ?? null,
                                'updated_at' => now(),
                                'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                            ]
                        );
                    }
                }
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

            if (!$return->trip_id) {
                foreach ($return->items as $item) {
                    $current = DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    $returnUnit = $item->unit_id ? Unit::find($item->unit_id) : null;

                    if (!$returnUnit || !$current) {
                        if ($current) {
                            DB::table('branch_product')
                                ->where('branch_id', $return->branch_id)
                                ->where('product_id', $item->product_id)
                                ->increment('quantity', (float) $item->quantity);
                        } else {
                            DB::table('branch_product')->insert([
                                'branch_id' => $return->branch_id,
                                'product_id' => $item->product_id,
                                'quantity' => (float) $item->quantity,
                                'unit_id' => $item->unit_id,
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

                    $qtyInStockUnit = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $stockUnit->conversion_factor;

                    if (abs($qtyInStockUnit - round($qtyInStockUnit)) < 0.000001) {
                        DB::table('branch_product')
                            ->where('branch_id', $return->branch_id)
                            ->where('product_id', $item->product_id)
                            ->increment('quantity', (int) round($qtyInStockUnit));

                        continue;
                    }

                    $baseUnitId = $this->resolveBaseUnitId($stockUnit);
                    $baseUnit = Unit::find($baseUnitId);

                    if (!$baseUnit) {
                        continue;
                    }

                    $currentQtyInBase = (int) round(((float) $current->quantity * (float) $stockUnit->conversion_factor) / (float) $baseUnit->conversion_factor);
                    $returnQtyInBase = (int) round(((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $baseUnit->conversion_factor);

                    DB::table('branch_product')
                        ->where('id', $current->id)
                        ->update([
                            'unit_id' => $baseUnit->id,
                            'quantity' => $currentQtyInBase + $returnQtyInBase,
                            'updated_at' => now(),
                        ]);
                }
            }

            Customer::where('id', $return->customer_id)
                ->decrement('balance', (float) $return->refund_amount);

            if ($return->treasury_id && (float) $return->refund_amount > 0) {
                Treasury::where('id', $return->treasury_id)
                    ->decrement('balance', (float) $return->refund_amount);

                TreasuryTransaction::create([
                    'treasury_id' => $return->treasury_id,
                    'type' => 'withdrawal',
                    'amount' => (float) $return->refund_amount,
                    'description' => 'مرتجع مبيعات #' . $return->id,
                    'reference_number' => (string) $return->id,
                    'date' => now()->toDateString(),
                    'admin_id' => auth('admin')->id(),
                ]);

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

    /**
     * إعادة حساب مبلغ المرتجع بعد تطبيق خصم الطلب الأصلي.
     *
     * - يحسب نسبة الخصم من الطلب الأصلي بناءً على إجمالي بنود الطلب.
     * - يحدّث unit_price وrefund_amount لكل بند مرتجع.
     * - يحدّث subtotal وrefund_amount للمرتجع.
     * - إذا كان المرتجع مؤكداً/مستردّاً: يعكس أثر رصيد العميل القديم ويطبّق الجديد.
     */
    public function recalculateReturnRefund(int $id): SaleReturn
    {
        return DB::transaction(function () use ($id) {
            $return = $this->returnRepository->getById($id);

            if (!in_array($return->status, ['pending', 'confirmed', 'refunded'])) {
                throw new \Exception('لا يمكن إعادة حساب مرتجع ملغي');
            }

            $order = $return->order()->with('items')->first();

            if (!$order) {
                throw new \Exception('لم يتم العثور على الطلب الأصلي');
            }

            // ── حساب نسبة الخصم من إجمالي بنود الطلب (gross) ──────────────
            $grossSubtotal = (float) $order->items->sum(
                fn ($i) => (float) $i->quantity * (float) $i->unit_price
            );

            $discountAmount = (float) ($order->discount_amount ?? 0);
            $discountType   = strtolower((string) ($order->discount_type ?? ''));

            if ($discountAmount > 0 && $grossSubtotal > 0) {
                $discountValue = in_array($discountType, ['percentage', 'percent', '%'])
                    ? $grossSubtotal * ($discountAmount / 100)
                    : min($discountAmount, $grossSubtotal);
            } else {
                $discountValue = 0;
            }

            $discountRatio = $grossSubtotal > 0 ? $discountValue / $grossSubtotal : 0;

            // ── إعادة حساب كل بند ────────────────────────────────────────────
            $return->load('items.orderItem');
            $newSubtotal = 0;

            foreach ($return->items as $item) {
                // السعر الأصلي: من بند الطلب إذا كان متاحاً، وإلا من unit_price المخزّن
                $originalUnitPrice = $item->orderItem
                    ? (float) $item->orderItem->unit_price
                    : ((float) $item->unit_price / max(1 - $discountRatio, 0.000001));

                $netUnitPrice  = max(0, $originalUnitPrice * (1 - $discountRatio));
                $newRefundAmt  = round((float) $item->quantity * $netUnitPrice, 2);

                $item->update([
                    'unit_price'    => round($netUnitPrice, 6),
                    'refund_amount' => $newRefundAmt,
                ]);

                $newSubtotal += $newRefundAmt;
            }

            $newSubtotal = round($newSubtotal, 2);
            $oldRefund   = (float) $return->refund_amount;

            $return->update([
                'subtotal'      => $newSubtotal,
                'refund_amount' => $newSubtotal,
            ]);

            // ── تعديل رصيد العميل إذا كان المرتجع مؤكداً/مستردّاً ───────────
            if (in_array($return->status, ['confirmed', 'refunded'])) {
                $diff = $newSubtotal - $oldRefund;

                if (abs($diff) >= 0.01) {
                    if ($diff < 0) {
                        // المبلغ الجديد أقل → نرجع للعميل مبلغ أقل → نزيد رصيده (نرجع الفرق)
                        Customer::where('id', $return->customer_id)->increment('balance', abs($diff));
                    } else {
                        // المبلغ الجديد أكبر → نحسم أكثر من رصيده
                        Customer::where('id', $return->customer_id)->decrement('balance', $diff);
                    }
                }
            }

            return $return->fresh();
        });
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
