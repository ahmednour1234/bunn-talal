<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;

class RoleService
{
    public function __construct(protected RoleRepositoryInterface $roleRepository)
    {
    }

    public function getAllRoles()
    {
        return $this->roleRepository->getAll();
    }

    public function getRoleById(int $id)
    {
        return $this->roleRepository->getById($id);
    }

    public function createRole(array $data, array $permissionIds = [])
    {
        $role = $this->roleRepository->create($data);

        if (!empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }

    public function updateRole(int $id, array $data, array $permissionIds = [])
    {
        $role = $this->roleRepository->update($id, $data);
        $role->permissions()->sync($permissionIds);

        return $role;
    }

    public function deleteRole(int $id): bool
    {
        return $this->roleRepository->delete($id);
    }

    public function paginateRoles(int $perPage = 15, ?string $search = null)
    {
        return $this->roleRepository->paginate($perPage, $search);
    }
}
