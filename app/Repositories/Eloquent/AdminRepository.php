<?php

namespace App\Repositories\Eloquent;

use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        $query = $this->model->with(['roles', 'branch']);

        if ($search) {
            $query = $this->applySearch($query, $search);
        }

        return $query->latest()->paginate($perPage);
    }
}
