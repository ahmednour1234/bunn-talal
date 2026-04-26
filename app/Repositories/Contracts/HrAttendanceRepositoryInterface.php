<?php

namespace App\Repositories\Contracts;

interface HrAttendanceRepositoryInterface extends BaseRepositoryInterface
{
    public function forDelegate(int $delegateId, array $filters = []);
    public function summaryForDelegate(int $delegateId, string $month, int $year): array;
}
