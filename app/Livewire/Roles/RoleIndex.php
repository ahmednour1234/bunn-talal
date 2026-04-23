<?php

namespace App\Livewire\Roles;

use App\Services\RoleService;
use Livewire\Component;
use Livewire\WithPagination;

class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(int $id, RoleService $roleService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('roles.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $role = $roleService->getRoleById($id);
        if ($role->name === 'super-admin') {
            session()->flash('error', 'لا يمكن حذف دور المدير العام');
            return;
        }

        $roleService->deleteRole($id);
        session()->flash('success', 'تم حذف الدور بنجاح');
    }

    public function render(RoleService $roleService)
    {
        return view('livewire.roles.role-index', [
            'roles' => $roleService->paginateRoles(10, $this->search ?: null),
        ]);
    }
}
