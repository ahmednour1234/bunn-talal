<?php

namespace App\Repositories\Eloquent;

use App\Models\TripBookingRequest;
use App\Repositories\Contracts\BookingRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingRequestRepository extends BaseRepository implements BookingRequestRepositoryInterface
{
    public function __construct(TripBookingRequest $model)
    {
        parent::__construct($model);
    }

    public function getById(int $id): TripBookingRequest
    {
        return $this->model
            ->with([
                'items.product:id,name,image',
                'items.unit:id,name,symbol',
                'convertedOrder:id,order_number,status,total',
                'trip:id,trip_number,status',
            ])
            ->findOrFail($id);
    }

    public function getDelegateRequests(int $delegateId, ?int $tripId): Collection
    {
        $query = $this->model
            ->where('delegate_id', $delegateId)
            ->with([
                'items.product:id,name,image',
                'items.unit:id,name,symbol',
                'trip:id,trip_number,status',
            ]);

        if ($tripId !== null) {
            $query->where('trip_id', $tripId);
        }

        return $query->latest()->get();
    }

    public function create(array $data): TripBookingRequest
    {
        return $this->model->create($data);
    }
}
