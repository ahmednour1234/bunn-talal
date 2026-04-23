<?php

namespace App\Services;

use App\Models\Treasury;
use App\Repositories\Contracts\TreasuryTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TreasuryTransactionService
{
    public function __construct(protected TreasuryTransactionRepositoryInterface $transactionRepository)
    {
    }

    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $transaction = $this->transactionRepository->create($data);

            $treasury = Treasury::findOrFail($data['treasury_id']);
            if ($data['type'] === 'deposit') {
                $treasury->increment('balance', $data['amount']);
            } else {
                $treasury->decrement('balance', $data['amount']);
            }

            return $transaction;
        });
    }

    public function paginateTransactions(int $perPage = 15, ?string $search = null)
    {
        return $this->transactionRepository->paginate($perPage, $search);
    }
}
