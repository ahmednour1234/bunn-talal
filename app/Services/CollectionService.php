<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Customer;
use App\Models\SaleOrder;
use Illuminate\Support\Facades\DB;

class CollectionService
{
    /**
     * Create a collection record and update corresponding sale orders.
     */
    public function create(array $data, array $items): Collection
    {
        return DB::transaction(function () use ($data, $items) {
            $totalAmount = collect($items)->sum(fn($i) => (float) $i['amount']);

            $collection = Collection::create(array_merge($data, [
                'total_amount' => round($totalAmount, 2),
                'status'       => 'completed',
            ]));

            foreach ($items as $item) {
                $collection->items()->create([
                    'sale_order_id' => $item['sale_order_id'] ?? null,
                    'amount'        => $item['amount'],
                    'notes'         => $item['notes'] ?? null,
                ]);

                // Update sale order paid_amount and status
                if (!empty($item['sale_order_id'])) {
                    $order = SaleOrder::find($item['sale_order_id']);
                    if ($order && !in_array($order->status, ['cancelled', 'paid'])) {
                        $newPaid = round((float)$order->paid_amount + (float)$item['amount'], 2);
                        $newPaid = min($newPaid, (float)$order->total);
                        $order->update([
                            'paid_amount' => $newPaid,
                            'status'      => $newPaid >= (float)$order->total ? 'paid' : 'partial_paid',
                        ]);

                        // Reduce customer balance (they've paid)
                        Customer::where('id', $order->customer_id)
                            ->decrement('balance', (float)$item['amount']);
                    }
                }
            }

            return $collection->load(['items.saleOrder', 'customer:id,name,phone', 'delegate:id,name']);
        });
    }

    public function getByTrip(int $tripId, int $delegateId)
    {
        return Collection::where('trip_id', $tripId)
            ->where('delegate_id', $delegateId)
            ->with(['customer:id,name,phone', 'items.saleOrder:id,order_number,total,paid_amount'])
            ->latest()
            ->get();
    }

    public function getById(int $id): Collection
    {
        return Collection::with(['items.saleOrder:id,order_number,total,paid_amount', 'customer:id,name,phone'])->findOrFail($id);
    }
}
