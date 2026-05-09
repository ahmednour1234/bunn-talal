<?php

namespace App\Repositories\Contracts;

use App\Models\TripBookingRequest;
use Illuminate\Database\Eloquent\Collection;

interface BookingRequestRepositoryInterface
{
    public function getById(int $id): TripBookingRequest;

    public function getDelegateRequests(int $delegateId, ?int $tripId): Collection;

    public function create(array $data): TripBookingRequest;
}
