<?php

namespace App\Services;

use App\Repositories\Contracts\SupplierRepositoryInterface;

class SupplierService
{
    public function __construct(protected SupplierRepositoryInterface $supplierRepository)
    {
    }

    public function getAllSuppliers()
    {
        return $this->supplierRepository->getAll();
    }

    public function getSupplierById(int $id)
    {
        return $this->supplierRepository->getById($id);
    }

    public function createSupplier(array $data)
    {
        return $this->supplierRepository->create($data);
    }

    public function updateSupplier(int $id, array $data)
    {
        return $this->supplierRepository->update($id, $data);
    }

    public function deleteSupplier(int $id): bool
    {
        return $this->supplierRepository->delete($id);
    }

    public function paginateSuppliers(int $perPage = 15, ?string $search = null)
    {
        return $this->supplierRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $supplier = $this->supplierRepository->getById($id);
        $supplier->update(['is_active' => !$supplier->is_active]);
        return $supplier;
    }
}
