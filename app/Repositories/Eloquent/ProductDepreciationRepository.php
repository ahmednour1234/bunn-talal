<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductDepreciation;
use App\Repositories\Contracts\ProductDepreciationRepositoryInterface;

class ProductDepreciationRepository extends BaseRepository implements ProductDepreciationRepositoryInterface
{
    public function __construct(ProductDepreciation $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId)
    {
        $query = $this->model->with(['branch', 'admin', 'approvedByAdmin']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('depreciation_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->latest()->paginate($perPage);
    }
}
