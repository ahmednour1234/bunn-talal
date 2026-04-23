<?php

namespace App\Livewire\PurchaseReturns;

use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Services\PurchaseReturnService;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseReturnIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $supplierFilter = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingSupplierFilter() { $this->resetPage(); }

    public function confirmReturn(int $id, PurchaseReturnService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('purchase-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }

        try {
            $service->confirmReturn($id);
            session()->flash('success', 'تم تأكيد المرتجع بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelReturn(int $id, PurchaseReturnService $service)
    {
        try {
            $service->cancelReturn($id);
            session()->flash('success', 'تم إلغاء المرتجع');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(PurchaseReturnService $service)
    {
        $supplierId = $this->supplierFilter ? (int) $this->supplierFilter : null;

        return view('livewire.purchase-returns.purchase-return-index', [
            'returns' => $service->paginateWithFilters(
                10,
                $this->search ?: null,
                $this->statusFilter ?: null,
                $supplierId
            ),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => PurchaseReturn::statusLabels(),
            'summaryStats' => $service->getSummaryStats(
                $this->search ?: null,
                $this->statusFilter ?: null,
                $supplierId
            ),
        ]);
    }
}
