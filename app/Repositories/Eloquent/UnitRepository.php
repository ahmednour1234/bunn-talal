<?php

namespace App\Repositories\Eloquent;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;

class UnitRepository extends BaseRepository implements UnitRepositoryInterface
{
    public function __construct(Unit $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('symbol', 'like', "%{$search}%");
        });
    }
}
