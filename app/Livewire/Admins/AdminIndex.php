<?php

namespace App\Livewire\Admins;

use App\Services\AdminService;
use Livewire\Component;
use Livewire\WithPagination;

class AdminIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(int $id, AdminService $adminService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('admins.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        if ($admin->id === $id) {
            session()->flash('error', 'لا يمكنك حذف حسابك الخاص');
            return;
        }

        $adminService->deleteAdmin($id);
        session()->flash('success', 'تم حذف المدير بنجاح');
    }

    public function render(AdminService $adminService)
    {
        return view('livewire.admins.admin-index', [
            'admins' => $adminService->paginateAdmins(10, $this->search ?: null),
        ]);
    }
}
