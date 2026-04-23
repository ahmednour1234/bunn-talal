<?php

namespace App\Livewire\SaleOrders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Delegate;
use App\Models\SaleOrder;
use App\Services\SaleOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class SaleOrderIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $customerFilter = '';
    public string $branchFilter = '';
    public string $delegateFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCustomerFilter() { $this->resetPage(); }
    public function updatingBranchFilter() { $this->resetPage(); }
    public function updatingDelegateFilter() { $this->resetPage(); }

    public function cancelOrder(int $id, SaleOrderService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('sale-orders.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }

        try {
            $service->cancelOrder($id);
            session()->flash('success', 'تم إلغاء الطلب بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(SaleOrderService $service)
    {
        $customerId  = $this->customerFilter  ? (int) $this->customerFilter  : null;
        $branchId    = $this->branchFilter    ? (int) $this->branchFilter    : null;
        $delegateId  = $this->delegateFilter  ? (int) $this->delegateFilter  : null;

        return view('livewire.sale-orders.sale-order-index', [
            'orders' => $service->paginateWithFilters(
                10,
                $this->search ?: null,
                $this->statusFilter ?: null,
                $customerId,
                $branchId,
                $delegateId,
                $this->dateFrom ?: null,
                $this->dateTo ?: null,
            ),
            'customers'    => Customer::where('is_active', true)->orderBy('name')->get(),
            'branches'     => Branch::where('is_active', true)->orderBy('name')->get(),
            'delegates'    => Delegate::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => SaleOrder::statusLabels(),
            'statusSummary' => $service->getStatusSummary(
                $this->search ?: null,
                $this->statusFilter ?: null,
                $customerId,
                $branchId,
            ),
        ]);
    }
}
