<?php

namespace App\Repositories\Eloquent;

use App\Models\Delegate;
use App\Repositories\Contracts\DelegateRepositoryInterface;

class DelegateRepository extends BaseRepository implements DelegateRepositoryInterface
{
    public function __construct(Delegate $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?Delegate
    {
        return $this->model->where('email', $email)->first();
    }

    public function updateLocation(int $id, float $latitude, float $longitude): bool
    {
        return (bool) $this->model->where('id', $id)->update([
            'current_latitude'    => $latitude,
            'current_longitude'   => $longitude,
            'location_updated_at' => now(),
        ]);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('national_id', 'like', "%{$search}%");
        });
    }
}
