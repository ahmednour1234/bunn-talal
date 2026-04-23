<?php

namespace App\Repositories\Contracts;

use App\Models\Delegate;

interface DelegateRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?Delegate;
}
