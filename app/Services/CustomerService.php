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

    public function createCustomer(array $data, array $delegateIds = [])
    {
        $customer = $this->customerRepository->create($data);
        $customer->delegates()->sync($delegateIds);
        return $customer;
    }

    public function updateCustomer(int $id, array $data, array $delegateIds = [])
    {
        $customer = $this->customerRepository->update($id, $data);
        $customer->delegates()->sync($delegateIds);
        return $customer;
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

    public function getTrashedCustomers()
    {
        return $this->customerRepository->getTrashed();
    }

    public function restoreCustomer(int $id): bool
    {
        return $this->customerRepository->restore($id);
    }

    public function forceDeleteCustomer(int $id): bool
    {
        return $this->customerRepository->forceDelete($id);
    }
}
