<?php

namespace App\Repositories\Eloquent;

use App\Models\SaleOrder;
use App\Repositories\Contracts\SaleOrderRepositoryInterface;

class SaleOrderRepository extends BaseRepository implements SaleOrderRepositoryInterface
{
    public function __construct(SaleOrder $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId, ?int $delegateId, ?string $dateFrom, ?string $dateTo)
    {
        $query = $this->model->with(['customer', 'branch', 'delegate', 'admin', 'treasury']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
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

        if ($delegateId) {
            $query->where('delegate_id', $delegateId);
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query->latest()->paginate($perPage);
    }
}
