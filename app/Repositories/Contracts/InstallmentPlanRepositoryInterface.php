<?php

namespace App\Repositories\Contracts;

interface InstallmentPlanRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        int $perPage,
        ?string $search,
        ?string $status,
        ?string $partyType,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo
    );
}
