<?php

namespace App\Services;

use App\Repositories\Contracts\BranchRepositoryInterface;

class BranchService
{
    public function __construct(protected BranchRepositoryInterface $branchRepository)
    {
    }

    public function getAllBranches()
    {
        return $this->branchRepository->getAll();
    }

    public function getBranchById(int $id)
    {
        return $this->branchRepository->getById($id);
    }

    public function createBranch(array $data)
    {
        return $this->branchRepository->create($data);
    }

    public function updateBranch(int $id, array $data)
    {
        return $this->branchRepository->update($id, $data);
    }

    public function deleteBranch(int $id): bool
    {
        return $this->branchRepository->delete($id);
    }

    public function paginateBranches(int $perPage = 15, ?string $search = null)
    {
        return $this->branchRepository->paginate($perPage, $search);
    }
}
