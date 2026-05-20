<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Trip;
use App\Models\TripBookingRequest;
use App\Repositories\Contracts\BookingRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class BookingRequestService
{
    public function __construct(
        private readonly BookingRequestRepositoryInterface $repository,
    ) {}

    public function getDelegateRequests(int $delegateId, ?int $tripId): Collection
    {
        return $this->repository->getDelegateRequests($delegateId, $tripId);
    }

    public function getById(int $id): TripBookingRequest
    {
        return $this->repository->getById($id);
    }

    /**
     * Create a booking request.
     * - trip_id is auto-resolved from the delegate's active trip (or left null).
     * - unit_price per item is auto-filled from the product's selling_price.
     * - customer_name is optional.
     */
    public function create(array $data, int $delegateId): TripBookingRequest
    {
        return DB::transaction(function () use ($data, $delegateId) {
            // Duplicate guard: if a booking was already created in the last 60 seconds
            // by this delegate, return it instead of creating a duplicate.
            $recent = TripBookingRequest::where('delegate_id', $delegateId)
                ->where('created_at', '>=', now()->subMinute())
                ->latest()
                ->first();

            if ($recent) {
                return $recent->load([
                    'items.product:id,name,image',
                    'items.unit:id,name,symbol',
                    'trip:id,trip_number,status',
                ]);
            }

            // Resolve trip: prefer explicit trip_id → then active trip → null
            $tripId = $data['trip_id'] ?? null;
            if (!$tripId) {
                $tripId = Trip::where('delegate_id', $delegateId)
                    ->whereIn('status', ['active', 'in_transit'])
                    ->latest()
                    ->value('id');
            }

            $booking = $this->repository->create([
                'trip_id'          => $tripId,
                'delegate_id'      => $delegateId,
                'customer_name'    => $data['customer_name'] ?? null,
                'customer_phone'   => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'status'           => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                // Auto-fill unit_price from product selling_price if not provided
                $unitPrice = isset($item['unit_price']) && $item['unit_price'] !== null
                    ? (float) $item['unit_price']
                    : (float) (Product::find($item['product_id'])?->selling_price ?? 0);

                $booking->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_id'    => $item['unit_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'notes'      => $item['notes'] ?? null,
                ]);
            }

            $booking->load([
                'items.product:id,name,image',
                'items.unit:id,name,symbol',
                'trip:id,trip_number,status',
            ]);

            return $booking;
        });
    }

    public function cancel(int $id, int $delegateId): void
    {
        $booking = $this->repository->getById($id);

        if ($booking->delegate_id !== $delegateId) {
            throw new \RuntimeException('forbidden');
        }

        if ($booking->status !== 'pending') {
            throw new \RuntimeException('لا يمكن إلغاء هذا الطلب في وضعه الحالي');
        }

        $booking->update(['status' => 'cancelled']);
    }
}
