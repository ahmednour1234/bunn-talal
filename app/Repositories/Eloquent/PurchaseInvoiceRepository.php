<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseInvoice;
use App\Repositories\Contracts\PurchaseInvoiceRepositoryInterface;

class PurchaseInvoiceRepository extends BaseRepository implements PurchaseInvoiceRepositoryInterface
{
    public function __construct(PurchaseInvoice $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId, ?int $branchId)
    {
        $query = $this->model->with(['supplier', 'branch', 'admin', 'treasury']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->latest()->paginate($perPage);
    }
}
