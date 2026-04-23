<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SaleOrder;
use App\Models\Treasury;
use App\Repositories\Contracts\SaleOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SaleOrderService
{
    public function __construct(protected SaleOrderRepositoryInterface $orderRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->orderRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId, ?int $delegateId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        return $this->orderRepository->paginateWithFilters($perPage, $search, $status, $customerId, $branchId, $delegateId, $dateFrom, $dateTo);
    }

    public function getFilteredOrders(?string $search, ?string $status, ?int $customerId, ?int $branchId, ?int $delegateId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        return $this->buildFilteredQuery($search, $status, $customerId, $branchId, $delegateId, $dateFrom, $dateTo)
            ->with(['customer', 'branch', 'delegate', 'admin'])
            ->latest()
            ->get();
    }

    public function getStatusSummary(?string $search, ?string $status, ?int $customerId, ?int $branchId): array
    {
        $rows = $this->buildFilteredQuery($search, null, $customerId, $branchId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $result = [];
        foreach (SaleOrder::statusLabels() as $key => $label) {
            if ($status && $status !== $key) {
                $result[$key] = 0;
                continue;
            }
            $result[$key] = (int) ($rows[$key] ?? 0);
        }

        return $result;
    }

    protected function buildFilteredQuery(?string $search, ?string $status, ?int $customerId, ?int $branchId, ?int $delegateId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $query = SaleOrder::query()->with(['customer', 'branch', 'admin', 'delegate', 'treasury']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($delegateId) {
            $query->where('delegate_id', $delegateId);
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query;
    }

    public function createOrder(array $data, array $items, ?float $initialPayment = null): SaleOrder
    {
        return DB::transaction(function () use ($data, $items, $initialPayment) {
            $subtotal = 0;
            $totalTax = 0;

            foreach ($items as &$item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];

                if (!empty($item['discount']) && (float) $item['discount'] > 0) {
                    if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                        $lineTotal -= $lineTotal * ((float) $item['discount'] / 100);
                    } else {
                        $lineTotal -= (float) $item['discount'];
                    }
                }

                $itemTax = !empty($item['tax_amount']) ? (float) $item['tax_amount'] : 0;
                $lineTotal += $itemTax;
                $totalTax += $itemTax;

                $item['total'] = round($lineTotal, 2);
                $subtotal += $item['total'];
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            if ($discountAmount > 0 && ($data['discount_type'] ?? 'fixed') === 'percentage') {
                $discountAmount = $subtotal * ($discountAmount / 100);
            }

            $total = $subtotal - $discountAmount;

            $order = $this->orderRepository->create(array_merge($data, [
                'subtotal'        => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'tax_amount'      => round($totalTax, 2),
                'total'           => round($total, 2),
                'paid_amount'     => 0,
                'status'          => 'confirmed',
            ]));

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'] ?? null,
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'discount'      => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'tax_amount'    => $item['tax_amount'] ?? 0,
                    'total'         => $item['total'],
                ]);

                // Decrement stock from branch
                $stockRow = DB::table('branch_product')
                    ->where('branch_id', $data['branch_id'])
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($stockRow) {
                    $newQty = max(0, ((float) $stockRow->quantity) - ((float) $item['quantity']));
                    DB::table('branch_product')
                        ->where('id', $stockRow->id)
                        ->update(['quantity' => $newQty, 'updated_at' => now()]);
                }
            }

            // Increment customer balance (they owe us money)
            Customer::where('id', $data['customer_id'])->increment('balance', round($total, 2));

            // Process initial payment if any
            if ($initialPayment && $initialPayment > 0) {
                $this->processPayment($order, $initialPayment, $data['treasury_id'] ?? null, $data['admin_id']);
            } elseif (($data['payment_method'] ?? 'cash') === 'cash') {
                $this->processPayment($order, round($total, 2), $data['treasury_id'] ?? null, $data['admin_id']);
            }

            return $order->load(['items.product', 'items.unit', 'customer', 'branch']);
        });
    }

    protected function processPayment(SaleOrder $order, float $amount, ?int $treasuryId, int $adminId): void
    {
        if ($amount <= 0) return;

        $remaining = (float) $order->total - (float) $order->paid_amount;
        $payAmount = min($amount, $remaining);

        $order->payments()->create([
            'amount'         => $payAmount,
            'payment_date'   => now()->toDateString(),
            'treasury_id'    => $treasuryId,
            'payment_method' => $treasuryId ? 'cash' : 'credit',
            'admin_id'       => $adminId,
        ]);

        if ($treasuryId) {
            Treasury::where('id', $treasuryId)->increment('balance', $payAmount);
        }

        $newPaid = (float) $order->paid_amount + $payAmount;
        $order->update([
            'paid_amount' => $newPaid,
            'status'      => $newPaid >= (float) $order->total ? 'paid' : 'partial_paid',
        ]);

        // Decrease customer balance by paid amount
        Customer::where('id', $order->customer_id)->decrement('balance', $payAmount);
    }

    public function addPayment(int $orderId, array $paymentData): SaleOrder
    {
        return DB::transaction(function () use ($orderId, $paymentData) {
            $order = $this->orderRepository->getById($orderId);

            if (in_array($order->status, ['cancelled', 'paid'])) {
                throw new \Exception('لا يمكن إضافة دفعة لهذا الطلب');
            }

            $this->processPayment(
                $order,
                (float) $paymentData['amount'],
                $paymentData['treasury_id'] ?? null,
                $paymentData['admin_id']
            );

            return $order->fresh(['payments', 'customer']);
        });
    }

    public function cancelOrder(int $id): SaleOrder
    {
        return DB::transaction(function () use ($id) {
            $order = $this->orderRepository->getById($id);

            if ($order->status === 'cancelled') {
                throw new \Exception('الطلب ملغي بالفعل');
            }

            // Restore stock
            foreach ($order->items as $item) {
                $stockRow = DB::table('branch_product')
                    ->where('branch_id', $order->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($stockRow) {
                    DB::table('branch_product')
                        ->where('id', $stockRow->id)
                        ->update([
                            'quantity'   => ((float) $stockRow->quantity) + ((float) $item->quantity),
                            'updated_at' => now(),
                        ]);
                }
            }

            // Restore treasury (refund paid amount)
            foreach ($order->payments as $payment) {
                if ($payment->treasury_id) {
                    Treasury::where('id', $payment->treasury_id)->decrement('balance', $payment->amount);
                }
            }

            // Reset customer balance
            $remainingOwed = (float) $order->total - (float) $order->paid_amount;
            Customer::where('id', $order->customer_id)->decrement('balance', $remainingOwed);

            $order->update(['status' => 'cancelled']);

            return $order;
        });
    }
}
