<?php

namespace App\Repositories\Contracts;

interface HrLeaveRepositoryInterface extends BaseRepositoryInterface
{
    public function forDelegate(int $delegateId, array $filters = []);
    public function approve(int $id, int $adminId): mixed;
    public function reject(int $id, string $reason): mixed;
}
