<?php

namespace App\Services;

use App\Repositories\Contracts\CustomerRepositoryInterface;

class CustomerService
{
    public function __construct(protected CustomerRepositoryInterface $customerRepository)
    {
    }

    public function getAllCustomers()
    {
        return $this->customerRepository->getAll();
    }

    public function getCustomerById(int $id)
    {
        return $this->customerRepository->getById($id);
    }

    public function createCustomer(array $data)
    {
        return $this->customerRepository->create($data);
    }

    public function updateCustomer(int $id, array $data)
    {
        return $this->customerRepository->update($id, $data);
    }

    public function deleteCustomer(int $id): bool
    {
        return $this->customerRepository->delete($id);
    }

    public function paginateCustomers(int $perPage = 15, ?string $search = null)
    {
        return $this->customerRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $customer = $this->customerRepository->getById($id);
        $customer->update(['is_active' => !$customer->is_active]);
        return $customer;
    }
}
