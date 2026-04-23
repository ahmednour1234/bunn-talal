<?php

namespace App\Services;

use App\Repositories\Contracts\FinancialTransactionRepositoryInterface;

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
        return $this->transactionRepository->create($data);
    }

    public function updateTransaction(int $id, array $data)
    {
        return $this->transactionRepository->update($id, $data);
    }

    public function deleteTransaction(int $id): bool
    {
        return $this->transactionRepository->delete($id);
    }

    public function paginateTransactions(int $perPage = 15, ?string $search = null)
    {
        return $this->transactionRepository->paginate($perPage, $search);
    }
}
