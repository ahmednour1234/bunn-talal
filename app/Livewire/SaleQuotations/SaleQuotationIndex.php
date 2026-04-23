<?php

namespace App\Livewire\SaleQuotations;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Delegate;
use App\Models\SaleQuotation;
use App\Models\Treasury;
use App\Services\SaleQuotationService;
use Livewire\Component;
use Livewire\WithPagination;

class SaleQuotationIndex extends Component
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

    public function cancelQuotation(int $id, SaleQuotationService $service)
    {
        try {
            $service->cancelQuotation($id);
            session()->flash('success', 'تم رفض عرض السعر');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(SaleQuotationService $service)
    {
        $customerId = $this->customerFilter ? (int) $this->customerFilter : null;
        $branchId   = $this->branchFilter   ? (int) $this->branchFilter   : null;

        return view('livewire.sale-quotations.sale-quotation-index', [
            'quotations'   => $service->paginateWithFilters(10, $this->search ?: null, $this->statusFilter ?: null, $customerId, $branchId),
            'customers'    => Customer::where('is_active', true)->orderBy('name')->get(),
            'branches'     => Branch::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => SaleQuotation::statusLabels(),
        ]);
    }
}
