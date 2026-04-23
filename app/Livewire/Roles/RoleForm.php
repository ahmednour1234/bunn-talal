<?php

namespace App\Livewire\Roles;

use App\Services\PermissionService;
use App\Services\RoleService;
use Livewire\Component;

class RoleForm extends Component
{
    public ?int $roleId = null;
    public string $name = '';
    public string $display_name = '';
    public string $description = '';
    public array $selectedPermissions = [];

    public function mount(RoleService $roleService, ?int $id = null)
    {
        if ($id) {
            $this->roleId = $id;
            $role = $roleService->getRoleById($id);
            $this->name = $role->name;
            $this->display_name = $role->display_name;
            $this->description = $role->description ?? '';
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . ($this->roleId ?? 'NULL'),
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'selectedPermissions' => 'array',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم الدور مطلوب',
            'name.unique' => 'اسم الدور مستخدم مسبقاً',
            'display_name.required' => 'الاسم المعروض مطلوب',
        ];
    }

    public function save(RoleService $roleService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description ?: null,
        ];

        if ($this->roleId) {
            $roleService->updateRole($this->roleId, $data, $this->selectedPermissions);
            session()->flash('success', 'تم تحديث الدور بنجاح');
        } else {
            $roleService->createRole($data, $this->selectedPermissions);
            session()->flash('success', 'تم إضافة الدور بنجاح');
        }

        return redirect()->route('roles.index');
    }

    public function toggleAll(string $groupName, PermissionService $permissionService)
    {
        $grouped = $permissionService->getPermissionsGrouped();
        $groupPermissions = $grouped[$groupName] ?? collect();
        $groupIds = $groupPermissions->pluck('id')->toArray();

        $allSelected = empty(array_diff($groupIds, $this->selectedPermissions));

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $groupIds));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $groupIds)));
        }
    }

    public function render(PermissionService $permissionService)
    {
        return view('livewire.roles.role-form', [
            'permissionsGrouped' => $permissionService->getPermissionsGrouped(),
        ]);
    }
}
