<?php

namespace App\Repositories\Contracts;

interface PurchaseReturnRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId);
}
