<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$search}%"));
        });
    }

    public function getActiveProducts()
    {
        return $this->model->where('is_active', true)->with(['category', 'unit'])->get();
    }

    public function getProductsByBranch(int $branchId)
    {
        return $this->model->whereHas('branches', fn($q) => $q->where('branch_id', $branchId))->get();
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        $query = $this->model->with(['category', 'unit', 'tax', 'branches']);
        if ($search) {
            $query = $this->applySearch($query, $search);
        }
        return $query->latest()->paginate($perPage);
    }
}
