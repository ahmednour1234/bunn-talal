<?php

namespace App\Repositories\Eloquent;

use App\Models\FinancialTransaction;
use App\Repositories\Contracts\FinancialTransactionRepositoryInterface;

class FinancialTransactionRepository extends BaseRepository implements FinancialTransactionRepositoryInterface
{
    public function __construct(FinancialTransaction $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where('description', 'like', "%{$search}%");
    }
}
