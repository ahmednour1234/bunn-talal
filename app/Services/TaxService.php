<?php

namespace App\Services;

use App\Repositories\Contracts\TaxRepositoryInterface;

class TaxService
{
    public function __construct(protected TaxRepositoryInterface $taxRepository)
    {
    }

    public function getAllTaxes()
    {
        return $this->taxRepository->getAll();
    }

    public function getTaxById(int $id)
    {
        return $this->taxRepository->getById($id);
    }

    public function createTax(array $data)
    {
        return $this->taxRepository->create($data);
    }

    public function updateTax(int $id, array $data)
    {
        return $this->taxRepository->update($id, $data);
    }

    public function deleteTax(int $id): bool
    {
        return $this->taxRepository->delete($id);
    }

    public function paginateTaxes(int $perPage = 15, ?string $search = null)
    {
        return $this->taxRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $tax = $this->taxRepository->getById($id);
        $tax->update(['is_active' => !$tax->is_active]);
        return $tax;
    }
}
