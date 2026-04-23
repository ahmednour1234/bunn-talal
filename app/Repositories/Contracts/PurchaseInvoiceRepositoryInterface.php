<?php

namespace App\Repositories\Contracts;

interface PurchaseInvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId, ?int $branchId);
}
