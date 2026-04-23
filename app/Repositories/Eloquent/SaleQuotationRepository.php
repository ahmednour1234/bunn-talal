<?php

namespace App\Repositories\Eloquent;

use App\Models\SaleQuotation;
use App\Repositories\Contracts\SaleQuotationRepositoryInterface;

class SaleQuotationRepository extends BaseRepository implements SaleQuotationRepositoryInterface
{
    public function __construct(SaleQuotation $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId)
    {
        $query = $this->model->with(['customer', 'branch', 'delegate', 'admin']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
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

        return $query->latest()->paginate($perPage);
    }
}
