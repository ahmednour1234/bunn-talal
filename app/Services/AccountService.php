<?php

namespace App\Services;

use App\Repositories\Contracts\AccountRepositoryInterface;

class AccountService
{
    public function __construct(protected AccountRepositoryInterface $accountRepository)
    {
    }

    public function getAllAccounts()
    {
        return $this->accountRepository->getAll();
    }

    public function getAccountById(int $id)
    {
        return $this->accountRepository->getById($id);
    }

    public function createAccount(array $data)
    {
        return $this->accountRepository->create($data);
    }

    public function updateAccount(int $id, array $data)
    {
        return $this->accountRepository->update($id, $data);
    }

    public function deleteAccount(int $id): bool
    {
        return $this->accountRepository->delete($id);
    }

    public function paginateAccounts(int $perPage = 15, ?string $search = null)
    {
        return $this->accountRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $account = $this->accountRepository->getById($id);
        $account->update(['is_active' => !$account->is_active]);
        return $account;
    }
}
