<?php

namespace App\Livewire\Treasuries;

use App\Models\Treasury;
use App\Services\TreasuryService;
use Livewire\Component;
use Livewire\WithPagination;

class TreasuryIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, TreasuryService $treasuryService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('treasuries.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $treasury = $treasuryService->toggleActive($id);
        session()->flash('success', $treasury->is_active ? 'تم تفعيل الخزنة' : 'تم تعطيل الخزنة');
    }

    public function delete(int $id, TreasuryService $treasuryService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('treasuries.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $treasuryService->deleteTreasury($id);
        session()->flash('success', 'تم حذف الخزنة بنجاح');
    }

    public function render()
    {
        $query = Treasury::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        return view('livewire.treasuries.treasury-index', [
            'treasuries' => $query->latest()->paginate(10),
        ]);
    }
}
