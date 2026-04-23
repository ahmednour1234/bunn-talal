<?php

namespace App\Livewire\SaleReturns;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\SaleReturn;
use App\Services\SaleReturnService;
use Livewire\Component;
use Livewire\WithPagination;

class SaleReturnIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $customerFilter = '';
    public string $branchFilter = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCustomerFilter() { $this->resetPage(); }
    public function updatingBranchFilter() { $this->resetPage(); }

    public function confirmReturn(int $id, SaleReturnService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('sale-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->confirmReturn($id);
            session()->flash('success', 'تم تأكيد مرتجع المبيعات');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelReturn(int $id, SaleReturnService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('sale-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->cancelReturn($id);
            session()->flash('success', 'تم إلغاء مرتجع المبيعات');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(SaleReturnService $service)
    {
        $customerId = $this->customerFilter ? (int) $this->customerFilter : null;
        $branchId   = $this->branchFilter   ? (int) $this->branchFilter   : null;

        $stats = $service->getSummaryStats($this->search ?: null, $this->statusFilter ?: null, $customerId);

        return view('livewire.sale-returns.sale-return-index', [
            'returns'      => $service->paginateWithFilters(10, $this->search ?: null, $this->statusFilter ?: null, $customerId, $branchId),
            'customers'    => Customer::where('is_active', true)->orderBy('name')->get(),
            'branches'     => Branch::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => SaleReturn::statusLabels(),
            'stats'        => $stats,
        ]);
    }
}
