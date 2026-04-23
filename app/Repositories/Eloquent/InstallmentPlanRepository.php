<?php

namespace App\Repositories\Eloquent;

use App\Models\InstallmentPlan;
use App\Repositories\Contracts\InstallmentPlanRepositoryInterface;

class InstallmentPlanRepository extends BaseRepository implements InstallmentPlanRepositoryInterface
{
    public function __construct(InstallmentPlan $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        int $perPage,
        ?string $search,
        ?string $status,
        ?string $partyType,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo
    ) {
        $query = $this->model->with(['customer', 'supplier', 'branch', 'admin', 'treasury']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('plan_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($partyType) {
            $query->where('party_type', $partyType);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($dateFrom) {
            $query->whereDate('start_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('start_date', '<=', $dateTo);
        }

        return $query->latest()->paginate($perPage);
    }
}
