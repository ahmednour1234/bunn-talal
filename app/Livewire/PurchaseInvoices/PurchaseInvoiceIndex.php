<?php

namespace App\Livewire\PurchaseInvoices;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Branch;
use App\Services\PurchaseInvoiceService;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseInvoiceIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $supplierFilter = '';
    public string $branchFilter = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingSupplierFilter() { $this->resetPage(); }
    public function updatingBranchFilter() { $this->resetPage(); }

    public function cancelInvoice(int $id, PurchaseInvoiceService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('purchase-invoices.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }

        try {
            $service->cancelInvoice($id);
            session()->flash('success', 'تم إلغاء الفاتورة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(PurchaseInvoiceService $service)
    {
        $supplierId = $this->supplierFilter ? (int) $this->supplierFilter : null;
        $branchId = $this->branchFilter ? (int) $this->branchFilter : null;

        return view('livewire.purchase-invoices.purchase-invoice-index', [
            'invoices' => $service->paginateWithFilters(
                10,
                $this->search ?: null,
                $this->statusFilter ?: null,
                $supplierId,
                $branchId
            ),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => PurchaseInvoice::statusLabels(),
            'statusSummary' => $service->getStatusSummary(
                $this->search ?: null,
                $this->statusFilter ?: null,
                $supplierId,
                $branchId
            ),
        ]);
    }
}
