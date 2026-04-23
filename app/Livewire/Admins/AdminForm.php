<?php

namespace App\Livewire\Admins;

use App\Services\AdminService;
use App\Services\RoleService;
use Livewire\Component;

class AdminForm extends Component
{
    public ?int $adminId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    public function mount(AdminService $adminService, ?int $id = null)
    {
        if ($id) {
            $this->adminId = $id;
            $admin = $adminService->getAdminById($id);
            $this->name = $admin->name;
            $this->email = $admin->email;
            $this->selectedRoles = $admin->roles->pluck('id')->toArray();
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . ($this->adminId ?? 'NULL'),
            'selectedRoles' => 'array',
        ];

        if (!$this->adminId) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المدير مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ];
    }

    public function save(AdminService $adminService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->selectedRoles,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        if ($this->adminId) {
            $adminService->updateAdmin($this->adminId, $data);
            session()->flash('success', 'تم تحديث المدير بنجاح');
        } else {
            $adminService->createAdmin($data);
            session()->flash('success', 'تم إضافة المدير بنجاح');
        }

        return redirect()->route('admins.index');
    }

    public function render(RoleService $roleService)
    {
        return view('livewire.admins.admin-form', [
            'roles' => $roleService->getAllRoles(),
        ]);
    }
}
