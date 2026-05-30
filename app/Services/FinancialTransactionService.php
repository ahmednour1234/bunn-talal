<?php

namespace App\Services;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Repositories\Contracts\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FinancialTransactionService
{
    public function __construct(protected FinancialTransactionRepositoryInterface $transactionRepository)
    {
    }

    public function getTransactionById(int $id)
    {
        return $this->transactionRepository->getById($id);
    }

    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $transaction = $this->transactionRepository->create($data);

            if (!empty($data['treasury_id'])) {
                $treasuryType = $data['type'] === 'expense' ? 'withdrawal' : 'deposit';

                $treasuryTx = TreasuryTransaction::create([
                    'treasury_id'      => $data['treasury_id'],
                    'type'             => $treasuryType,
                    'amount'           => $data['amount'],
                    'description'      => $data['description'] ?? null,
                    'date'             => $data['date'],
                    'reference_number' => 'FT-' . $transaction->id,
                    'admin_id'         => $data['admin_id'] ?? null,
                ]);

                $treasury = Treasury::findOrFail($data['treasury_id']);
                if ($treasuryType === 'deposit') {
                    $treasury->increment('balance', $data['amount']);
                } else {
                    $treasury->decrement('balance', $data['amount']);
                }

                $transaction->update(['treasury_transaction_id' => $treasuryTx->id]);
            }

            return $transaction;
        });
    }

    public function updateTransaction(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $transaction = $this->transactionRepository->getById($id);
            $oldTreasuryId = $transaction->treasury_id;
            $oldAmount     = $transaction->amount;
            $oldType       = $transaction->type;

            // Remove old treasury transaction if exists
            if ($transaction->treasury_transaction_id) {
                $oldTreasuryTx = TreasuryTransaction::find($transaction->treasury_transaction_id);
                if ($oldTreasuryTx) {
                    $oldTreasury = Treasury::find($oldTreasuryTx->treasury_id);
                    if ($oldTreasury) {
                        if ($oldTreasuryTx->type === 'deposit') {
                            $oldTreasury->decrement('balance', $oldTreasuryTx->amount);
                        } else {
                            $oldTreasury->increment('balance', $oldTreasuryTx->amount);
                        }
                    }
                    $oldTreasuryTx->delete();
                }
                $data['treasury_transaction_id'] = null;
            }

            $this->transactionRepository->update($id, $data);
            $transaction->refresh();

            // Create new treasury transaction if treasury is set
            if (!empty($data['treasury_id'])) {
                $treasuryType = $data['type'] === 'expense' ? 'withdrawal' : 'deposit';

                $treasuryTx = TreasuryTransaction::create([
                    'treasury_id'      => $data['treasury_id'],
                    'type'             => $treasuryType,
                    'amount'           => $data['amount'],
                    'description'      => $data['description'] ?? null,
                    'date'             => $data['date'],
                    'reference_number' => 'FT-' . $id,
                    'admin_id'         => $data['admin_id'] ?? null,
                ]);

                $treasury = Treasury::findOrFail($data['treasury_id']);
                if ($treasuryType === 'deposit') {
                    $treasury->increment('balance', $data['amount']);
                } else {
                    $treasury->decrement('balance', $data['amount']);
                }

                $transaction->update(['treasury_transaction_id' => $treasuryTx->id]);
            }

            return $transaction;
        });
    }

    public function deleteTransaction(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $transaction = $this->transactionRepository->getById($id);

            if ($transaction->treasury_transaction_id) {
                $treasuryTx = TreasuryTransaction::find($transaction->treasury_transaction_id);
                if ($treasuryTx) {
                    $treasury = Treasury::find($treasuryTx->treasury_id);
                    if ($treasury) {
                        if ($treasuryTx->type === 'deposit') {
                            $treasury->decrement('balance', $treasuryTx->amount);
                        } else {
                            $treasury->increment('balance', $treasuryTx->amount);
                        }
                    }
                    $treasuryTx->delete();
                }
            }

            return $this->transactionRepository->delete($id);
        });
    }

    public function paginateTransactions(int $perPage = 15, ?string $search = null)
    {
        return $this->transactionRepository->paginate($perPage, $search);
    }
}

