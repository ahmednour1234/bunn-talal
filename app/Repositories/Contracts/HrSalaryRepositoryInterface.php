<?php

namespace App\Repositories\Contracts;

interface HrSalaryRepositoryInterface extends BaseRepositoryInterface
{
    public function forDelegate(int $delegateId, array $filters = []);
}
