<?php

namespace App\Livewire\InventoryDispatches;

use App\Models\Branch;
use App\Models\Delegate;
use App\Services\InventoryDispatchService;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryDispatchIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $branchFilter = '';
    public string $delegateFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function updatingDelegateFilter()
    {
        $this->resetPage();
    }

    public function delete(int $id, InventoryDispatchService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('inventory-dispatches.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $service->deleteDispatch($id);
        session()->flash('success', 'تم حذف أمر الصرف بنجاح');
    }

    public function render(InventoryDispatchService $service)
    {
        return view('livewire.inventory-dispatches.inventory-dispatch-index', [
            'dispatches' => $service->paginateWithFilters(
                10,
                $this->search ?: null,
                $this->statusFilter ?: null,
                $this->branchFilter ? (int) $this->branchFilter : null,
                $this->delegateFilter ? (int) $this->delegateFilter : null
            ),
            'branches' => Branch::where('is_active', true)->get(),
            'delegates' => Delegate::where('is_active', true)->get(),
        ]);
    }
}
