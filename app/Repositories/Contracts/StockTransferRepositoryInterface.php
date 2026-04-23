<?php

namespace App\Repositories\Contracts;

interface StockTransferRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId);
}
