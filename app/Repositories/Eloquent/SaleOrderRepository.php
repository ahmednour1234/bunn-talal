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

    public function getById(int $id)
    {
        return $this->model->with([
            'customer:id,name,phone,email',
            'items.product:id,name,image',
            'items.unit:id,name,symbol',
            'payments',
        ])->findOrFail($id);
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

    public function getDelegateOrdersForTrip(int $tripId, int $delegateId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->where('trip_id', $tripId)
            ->where('delegate_id', $delegateId)
            ->with(['customer:id,name,phone'])
            ->latest()
            ->get();
    }

    public function getDelegateOrders(int $delegateId, ?int $tripId): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model
            ->where('delegate_id', $delegateId)
            ->with(['customer:id,name,phone']);

        if ($tripId !== null) {
            $query->where('trip_id', $tripId);
        }

        return $query->latest()->get();
    }
}
