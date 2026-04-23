<?php

namespace App\Repositories\Contracts;

interface SaleOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId, ?int $delegateId, ?string $dateFrom, ?string $dateTo);
}
