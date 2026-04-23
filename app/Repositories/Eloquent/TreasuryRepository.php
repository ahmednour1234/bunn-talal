<?php

namespace App\Repositories\Eloquent;

use App\Models\Treasury;
use App\Repositories\Contracts\TreasuryRepositoryInterface;

class TreasuryRepository extends BaseRepository implements TreasuryRepositoryInterface
{
    public function __construct(Treasury $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
