<?php

namespace App\Repositories\Eloquent;

use App\Models\StockTransfer;
use App\Repositories\Contracts\StockTransferRepositoryInterface;

class StockTransferRepository extends BaseRepository implements StockTransferRepositoryInterface
{
    public function __construct(StockTransfer $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereHas('fromBranch', fn($b) => $b->where('name', 'like', "%{$search}%"))
              ->orWhereHas('toBranch', fn($b) => $b->where('name', 'like', "%{$search}%"))
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId)
    {
        $query = $this->model->with(['fromBranch', 'toBranch', 'requestedBy', 'items.product']);

        if ($search) {
            $query = $this->applySearch($query, $search);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        return $this->paginateWithFilters($perPage, $search, null, null);
    }
}
