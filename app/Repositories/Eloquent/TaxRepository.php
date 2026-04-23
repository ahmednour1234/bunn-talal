<?php

namespace App\Repositories\Eloquent;

use App\Models\Tax;
use App\Repositories\Contracts\TaxRepositoryInterface;

class TaxRepository extends BaseRepository implements TaxRepositoryInterface
{
    public function __construct(Tax $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }
}
