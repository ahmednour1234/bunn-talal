<?php

namespace App\Repositories\Eloquent;

use App\Models\Area;
use App\Repositories\Contracts\AreaRepositoryInterface;

class AreaRepository extends BaseRepository implements AreaRepositoryInterface
{
    public function __construct(Area $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
