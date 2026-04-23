<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseReturn;
use App\Repositories\Contracts\PurchaseReturnRepositoryInterface;

class PurchaseReturnRepository extends BaseRepository implements PurchaseReturnRepositoryInterface
{
    public function __construct(PurchaseReturn $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId)
    {
        $query = $this->model->with(['invoice', 'supplier', 'branch', 'admin']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('invoice', fn($i) => $i->where('invoice_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->latest()->paginate($perPage);
    }
}
