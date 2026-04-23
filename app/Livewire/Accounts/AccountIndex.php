<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Services\AccountService;
use Livewire\Component;
use Livewire\WithPagination;

class AccountIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, AccountService $accountService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('accounts.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $account = $accountService->toggleActive($id);
        session()->flash('success', $account->is_active ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب');
    }

    public function delete(int $id, AccountService $accountService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('accounts.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $accountService->deleteAccount($id);
        session()->flash('success', 'تم حذف الحساب بنجاح');
    }

    public function render()
    {
        $query = Account::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        return view('livewire.accounts.account-index', [
            'accounts' => $query->latest()->paginate(10),
        ]);
    }
}
