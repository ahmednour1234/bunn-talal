<?php

namespace App\Repositories\Contracts;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function getTrashed();
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
}
