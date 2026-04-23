<?php

namespace App\Repositories\Contracts;

interface ProductDepreciationRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $branchId);
}
