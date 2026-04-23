<?php

namespace App\Repositories\Contracts;

interface SaleReturnRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId);
}
