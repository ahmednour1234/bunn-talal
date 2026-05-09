<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Customer;
use App\Models\SaleOrder;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class CollectionService
{
    public function __construct(protected CollectionRepositoryInterface $collectionRepository) {}

    public function getById(int $id): Collection
    {
        return $this->collectionRepository->getById($id);
    }

    public function getDelegateCollections(int $delegateId, ?int $tripId = null): EloquentCollection
    {
        return $this->collectionRepository->getDelegateCollections($delegateId, $tripId);
    }

    /**
     * Create a collection record and update corresponding sale orders.
     */
    public function create(array $data, array $items): Collection
    {
        return DB::transaction(function () use ($data, $items) {
            $totalAmount = collect($items)->sum(fn($i) => (float) $i['amount']);

            $collection = $this->collectionRepository->create(array_merge($data, [
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
                        $newPaid = round((float) $order->paid_amount + (float) $item['amount'], 2);
                        $newPaid = min($newPaid, (float) $order->total);
                        $order->update([
                            'paid_amount' => $newPaid,
                            'status'      => $newPaid >= (float) $order->total ? 'paid' : 'partial_paid',
                        ]);

                        // Reduce customer balance (they've paid)
                        Customer::where('id', $order->customer_id)
                            ->decrement('balance', (float) $item['amount']);
                    }
                }
            }

            return $this->collectionRepository->getById($collection->id);
        });
    }
}

