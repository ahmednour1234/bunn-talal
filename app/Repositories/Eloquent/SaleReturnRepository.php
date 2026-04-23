<?php

namespace App\Repositories\Eloquent;

use App\Models\SaleReturn;
use App\Repositories\Contracts\SaleReturnRepositoryInterface;

class SaleReturnRepository extends BaseRepository implements SaleReturnRepositoryInterface
{
    public function __construct(SaleReturn $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId)
    {
        $query = $this->model->with(['order', 'customer', 'branch', 'admin']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('order', fn($o) => $o->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->latest()->paginate($perPage);
    }
}
