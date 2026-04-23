<?php

namespace App\Repositories\Eloquent;

use App\Models\TreasuryTransaction;
use App\Repositories\Contracts\TreasuryTransactionRepositoryInterface;

class TreasuryTransactionRepository extends BaseRepository implements TreasuryTransactionRepositoryInterface
{
    public function __construct(TreasuryTransaction $model)
    {
        parent::__construct($model);
    }

    protected function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('reference_number', 'like', "%{$search}%");
        });
    }
}
