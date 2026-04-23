<?php

namespace App\Livewire\StockTransfers;

use App\Models\Branch;
use App\Services\StockTransferService;
use Livewire\Component;
use Livewire\WithPagination;

class StockTransferIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $branchFilter = '';

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

    public function approve(int $id, StockTransferService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('stock-transfers.approve')) {
            session()->flash('error', 'ليس لديك صلاحية الموافقة');
            return;
        }

        try {
            $service->approveTransfer($id, $admin->id);
            session()->flash('success', 'تمت الموافقة على التحويل بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function reject(int $id, StockTransferService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('stock-transfers.approve')) {
            session()->flash('error', 'ليس لديك صلاحية الرفض');
            return;
        }

        try {
            $service->rejectTransfer($id, $admin->id);
            session()->flash('success', 'تم رفض التحويل');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function receive(int $id, StockTransferService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('stock-transfers.receive')) {
            session()->flash('error', 'ليس لديك صلاحية الاستلام');
            return;
        }

        try {
            $service->receiveTransfer($id, $admin->id);
            session()->flash('success', 'تم استلام التحويل بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(StockTransferService $service)
    {
        return view('livewire.stock-transfers.stock-transfer-index', [
            'transfers' => $service->paginateWithFilters(
                10,
                $this->search ?: null,
                $this->statusFilter ?: null,
                $this->branchFilter ? (int) $this->branchFilter : null
            ),
            'branches' => Branch::where('is_active', true)->get(),
        ]);
    }
}
