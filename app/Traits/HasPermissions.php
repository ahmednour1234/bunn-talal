<?php

namespace App\Traits;

trait HasPermissions
{
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles()->with('permissions')->get()
            ->pluck('permissions')->flatten()->unique('id');
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->getAllPermissions()->contains('name', $permissionName);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
}
