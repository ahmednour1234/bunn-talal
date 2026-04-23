<?php

namespace App\Livewire\Permissions;

use App\Services\PermissionService;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showCreate = false;
    public string $newName = '';
    public string $newDisplayName = '';
    public string $newGroupName = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create(PermissionService $permissionService)
    {
        $this->validate([
            'newName' => 'required|string|max:255|unique:permissions,name',
            'newDisplayName' => 'required|string|max:255',
            'newGroupName' => 'required|string|max:255',
        ], [
            'newName.required' => 'اسم الصلاحية مطلوب',
            'newName.unique' => 'اسم الصلاحية مستخدم مسبقاً',
            'newDisplayName.required' => 'الاسم المعروض مطلوب',
            'newGroupName.required' => 'اسم المجموعة مطلوب',
        ]);

        $permissionService->createPermission([
            'name' => $this->newName,
            'display_name' => $this->newDisplayName,
            'group_name' => $this->newGroupName,
        ]);

        $this->reset(['newName', 'newDisplayName', 'newGroupName', 'showCreate']);
        session()->flash('success', 'تم إضافة الصلاحية بنجاح');
    }

    public function delete(int $id, PermissionService $permissionService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('permissions.create')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $permissionService->deletePermission($id);
        session()->flash('success', 'تم حذف الصلاحية بنجاح');
    }

    public function render(PermissionService $permissionService)
    {
        return view('livewire.permissions.permission-index', [
            'permissionsGrouped' => $permissionService->getPermissionsGrouped(),
        ]);
    }
}
