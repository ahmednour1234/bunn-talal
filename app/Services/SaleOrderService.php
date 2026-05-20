<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DelegateLoan;
use App\Models\SaleOrder;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Models\Trip;
use App\Repositories\Contracts\SaleOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SaleOrderService
{
    public function __construct(protected SaleOrderRepositoryInterface $orderRepository)
    {
    }

    public function getDelegateOrdersForTrip(int $tripId, int $delegateId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orderRepository->getDelegateOrdersForTrip($tripId, $delegateId);
    }

    public function getDelegateOrders(int $delegateId, ?int $tripId = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orderRepository->getDelegateOrders($delegateId, $tripId);
    }

    /**
     * Find the delegate's active trip, or create one if none exists.
     */
    public function resolveOrCreateActiveTrip(int $delegateId, ?int $branchId = null): Trip
    {
        $trip = Trip::where('delegate_id', $delegateId)
            ->whereIn('status', ['active', 'in_transit'])
            ->latest()
            ->first();

        if (!$trip) {
            $trip = Trip::create([
                'delegate_id' => $delegateId,
                'branch_id'   => $branchId,
                'admin_id'    => null,
                'status'      => 'active',
                'start_date'  => now()->toDateString(),
            ]);
        }

        return $trip;
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

                // Decrement stock from delegate (delegate sale) or branch (direct branch sale)
                if (!empty($data['delegate_id'])) {
                    $exists = DB::table('delegate_product')
                        ->where('delegate_id', $data['delegate_id'])
                        ->where('product_id', $item['product_id'])
                        ->exists();

                    if ($exists) {
                        DB::table('delegate_product')
                            ->where('delegate_id', $data['delegate_id'])
                            ->where('product_id', $item['product_id'])
                            ->decrement('quantity', (float) $item['quantity']);
                    } else {
                        DB::table('delegate_product')->insert([
                            'delegate_id' => $data['delegate_id'],
                            'product_id'  => $item['product_id'],
                            'quantity'    => -((float) $item['quantity']),
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                } else {
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

    protected function processPayment(SaleOrder $order, float $amount, ?int $treasuryId, ?int $adminId): void
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
            TreasuryTransaction::create([
                'treasury_id'      => $treasuryId,
                'type'             => 'deposit',
                'amount'           => $payAmount,
                'description'      => 'سداد طلب مبيعات #' . $order->id,
                'reference_number' => (string) $order->id,
                'date'             => now()->toDateString(),
                'admin_id'         => $adminId,
            ]);
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

            // Restore stock based on trip settlement status
            $this->restoreStockOnCancellation($order);

            // Restore treasury (refund paid amount)
            foreach ($order->payments as $payment) {
                if ($payment->treasury_id) {
                    Treasury::where('id', $payment->treasury_id)->decrement('balance', $payment->amount);
                    TreasuryTransaction::create([
                        'treasury_id'      => $payment->treasury_id,
                        'type'             => 'withdrawal',
                        'amount'           => $payment->amount,
                        'description'      => 'إلغاء طلب مبيعات #' . $order->id,
                        'reference_number' => (string) $order->id,
                        'date'             => now()->toDateString(),
                        'admin_id'         => auth('admin')->id(),
                    ]);
                }
            }

            // Reset customer balance
            $remainingOwed = (float) $order->total - (float) $order->paid_amount;
            Customer::where('id', $order->customer_id)->decrement('balance', $remainingOwed);

            $order->update(['status' => 'cancelled']);

            return $order;
        });
    }

    public function confirmCancellation(int $id): SaleOrder
    {
        return DB::transaction(function () use ($id) {
            $order = $this->orderRepository->getById($id);

            if ($order->status !== 'cancellation_pending') {
                throw new \Exception('الطلب ليس في حالة انتظار الإلغاء');
            }

            // Restore stock based on trip settlement status
            $this->restoreStockOnCancellation($order);

            // Restore treasury (refund paid amount)
            foreach ($order->payments as $payment) {
                if ($payment->treasury_id) {
                    Treasury::where('id', $payment->treasury_id)->decrement('balance', $payment->amount);
                    TreasuryTransaction::create([
                        'treasury_id'      => $payment->treasury_id,
                        'type'             => 'withdrawal',
                        'amount'           => $payment->amount,
                        'description'      => 'إلغاء طلب مبيعات #' . $order->id,
                        'reference_number' => (string) $order->id,
                        'date'             => now()->toDateString(),
                        'admin_id'         => auth('admin')->id(),
                    ]);
                }
            }

            // Reset customer balance
            $remainingOwed = (float) $order->total - (float) $order->paid_amount;
            Customer::where('id', $order->customer_id)->decrement('balance', $remainingOwed);

            $order->update(['status' => 'cancelled']);

            // Reverse delegate loans linked to this order (mark as paid = no longer owed)
            if ($order->delegate_id) {
                DelegateLoan::where('sale_order_id', $order->id)
                    ->where('is_paid', false)
                    ->each(function (DelegateLoan $loan) {
                        $loan->update([
                            'paid_amount' => $loan->amount,
                            'is_paid'     => true,
                            'paid_at'     => now()->toDateString(),
                            'note'        => $loan->note . ' (مُلغاة)',
                        ]);
                    });
            }

            return $order;
        });
    /**
     * Restore sold quantities on order cancellation.
     *
     * Rule:
     *  - Trip exists AND not yet settled → return to delegate_product (delegate still holds the goods)
     *  - Trip settled OR no trip         → return to branch_product   (goods are back in the warehouse)
     */
    private function restoreStockOnCancellation(SaleOrder $order): void
    {
        $trip = $order->trip_id ? $order->trip : null;
        $returnToDelegate = $order->delegate_id
            && $trip
            && $trip->status !== 'settled';

        foreach ($order->items as $item) {
            if ($returnToDelegate) {
                DB::table('delegate_product')
                    ->where('delegate_id', $order->delegate_id)
                    ->where('product_id', $item->product_id)
                    ->increment('quantity', (float) $item->quantity);
            } else {
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
                } else {
                    DB::table('branch_product')->insert([
                        'branch_id'  => $order->branch_id,
                        'product_id' => $item->product_id,
                        'quantity'   => (float) $item->quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}

