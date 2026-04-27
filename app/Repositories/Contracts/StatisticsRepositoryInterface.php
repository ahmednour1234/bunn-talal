<?php

namespace App\Repositories\Contracts;

interface StatisticsRepositoryInterface
{
    public function delegateStatistics(int $delegateId, array $filters = []): array;
    public function delegateHrStatistics(int $delegateId, array $filters = []): array;
}
