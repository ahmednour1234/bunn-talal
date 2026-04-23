<?php

namespace App\Livewire\Branches;

use App\Services\BranchService;
use Livewire\Component;
use Livewire\WithPagination;

class BranchIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(int $id, BranchService $branchService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('branches.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $branchService->deleteBranch($id);
        session()->flash('success', 'تم حذف الفرع بنجاح');
    }

    public function render(BranchService $branchService)
    {
        return view('livewire.branches.branch-index', [
            'branches' => $branchService->paginateBranches(10, $this->search ?: null),
        ]);
    }
}
