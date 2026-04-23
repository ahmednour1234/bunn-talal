<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveProducts();
    public function getProductsByBranch(int $branchId);
}
