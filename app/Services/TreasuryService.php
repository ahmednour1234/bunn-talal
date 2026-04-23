<?php

namespace App\Services;

use App\Repositories\Contracts\TreasuryRepositoryInterface;

class TreasuryService
{
    public function __construct(protected TreasuryRepositoryInterface $treasuryRepository)
    {
    }

    public function getAllTreasuries()
    {
        return $this->treasuryRepository->getAll();
    }

    public function getTreasuryById(int $id)
    {
        return $this->treasuryRepository->getById($id);
    }

    public function createTreasury(array $data)
    {
        return $this->treasuryRepository->create($data);
    }

    public function updateTreasury(int $id, array $data)
    {
        return $this->treasuryRepository->update($id, $data);
    }

    public function deleteTreasury(int $id): bool
    {
        return $this->treasuryRepository->delete($id);
    }

    public function paginateTreasuries(int $perPage = 15, ?string $search = null)
    {
        return $this->treasuryRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $treasury = $this->treasuryRepository->getById($id);
        $treasury->update(['is_active' => !$treasury->is_active]);
        return $treasury;
    }
}
