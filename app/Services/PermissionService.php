<?php

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;

class PermissionService
{
    public function __construct(protected PermissionRepositoryInterface $permissionRepository)
    {
    }

    public function getAllPermissions()
    {
        return $this->permissionRepository->getAll();
    }

    public function getPermissionById(int $id)
    {
        return $this->permissionRepository->getById($id);
    }

    public function getPermissionsGrouped()
    {
        return $this->permissionRepository->getAll()->groupBy('group_name');
    }

    public function createPermission(array $data)
    {
        return $this->permissionRepository->create($data);
    }

    public function deletePermission(int $id): bool
    {
        return $this->permissionRepository->delete($id);
    }

    public function paginatePermissions(int $perPage = 15, ?string $search = null)
    {
        return $this->permissionRepository->paginate($perPage, $search);
    }
}
