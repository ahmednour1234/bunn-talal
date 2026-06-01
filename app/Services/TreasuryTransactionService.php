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
            if ($data['type'] === 'deposit' || $data['type'] === 'transfer_in') {
                $treasury->increment('balance', $data['amount']);
            } else {
                $treasury->decrement('balance', $data['amount']);
            }

            return $transaction;
        });
    }

    public function transferBetweenTreasuries(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $from = Treasury::findOrFail($data['from_treasury_id']);
            $to   = Treasury::findOrFail($data['to_treasury_id']);

            if ($from->balance < $data['amount']) {
                throw new \Exception('رصيد الخزنة غير كافٍ للتحويل');
            }

            $ref  = $data['reference_number'] ?? null;
            $note = $data['description'] ?? null;
            $date = $data['date'];
            $admin = $data['admin_id'];

            $out = $this->transactionRepository->create([
                'treasury_id'      => $from->id,
                'type'             => 'transfer_out',
                'amount'           => $data['amount'],
                'description'      => 'تحويل إلى ' . $to->name . ($note ? ' - ' . $note : ''),
                'date'             => $date,
                'reference_number' => $ref,
                'admin_id'         => $admin,
            ]);

            $in = $this->transactionRepository->create([
                'treasury_id'      => $to->id,
                'type'             => 'transfer_in',
                'amount'           => $data['amount'],
                'description'      => 'تحويل من ' . $from->name . ($note ? ' - ' . $note : ''),
                'date'             => $date,
                'reference_number' => $ref,
                'admin_id'         => $admin,
            ]);

            $from->decrement('balance', $data['amount']);
            $to->increment('balance', $data['amount']);

            return [$out, $in];
        });
    }

    public function paginateTransactions(int $perPage = 15, ?string $search = null)
    {
        return $this->transactionRepository->paginate($perPage, $search);
    }
}
