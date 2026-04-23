<?php

namespace App\Repositories\Eloquent;

use App\Models\InventoryDispatch;
use App\Repositories\Contracts\InventoryDispatchRepositoryInterface;

class InventoryDispatchRepository extends BaseRepository implements InventoryDispatchRepositoryInterface
{
    public function __construct(InventoryDispatch $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereHas('delegate', fn($d) => $d->where('name', 'like', "%{$search}%"))
              ->orWhereHas('branch', fn($b) => $b->where('name', 'like', "%{$search}%"))
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId, ?int $delegateId)
    {
        $query = $this->model->with(['branch', 'delegate', 'admin', 'items.product']);

        if ($search) {
            $query = $this->applySearch($query, $search);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($delegateId) {
            $query->where('delegate_id', $delegateId);
        }

        return $query->latest()->paginate($perPage);
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        return $this->paginateWithFilters($perPage, $search, null, null, null);
    }

    public function getById(int $id)
    {
        return $this->model->with(['branch', 'delegate', 'admin', 'items.product.unit', 'trip.settler'])->findOrFail($id);
    }
}
