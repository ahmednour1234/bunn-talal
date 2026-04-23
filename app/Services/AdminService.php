<?php

namespace App\Services;

use App\Repositories\Contracts\AdminRepositoryInterface;

class AdminService
{
    public function __construct(protected AdminRepositoryInterface $adminRepository)
    {
    }

    public function getAllAdmins()
    {
        return $this->adminRepository->getAll();
    }

    public function getAdminById(int $id)
    {
        return $this->adminRepository->getById($id);
    }

    public function createAdmin(array $data)
    {
        $admin = $this->adminRepository->create($data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        return $admin;
    }

    public function updateAdmin(int $id, array $data)
    {
        $admin = $this->adminRepository->update($id, $data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        return $admin;
    }

    public function deleteAdmin(int $id): bool
    {
        return $this->adminRepository->delete($id);
    }

    public function paginateAdmins(int $perPage = 15, ?string $search = null)
    {
        return $this->adminRepository->paginate($perPage, $search);
    }
}
