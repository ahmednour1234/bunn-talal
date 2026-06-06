<?php

namespace App\Livewire\PurchaseReturns;

use App\Services\PurchaseReturnService;
use Livewire\Component;

class PurchaseReturnShow extends Component
{
    public int $returnId;

    public function mount(int $id): void
    {
        $this->returnId = $id;
    }

    public function confirmReturn(PurchaseReturnService $service): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('purchase-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->confirmReturn($this->returnId);
            session()->flash('success', 'تم تأكيد المرتجع بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelReturn(PurchaseReturnService $service): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('purchase-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->cancelReturn($this->returnId);
            session()->flash('success', 'تم إلغاء المرتجع');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(PurchaseReturnService $service)
    {
        $return = $service->getById($this->returnId);
        $return->load([
            'items.product',
            'items.unit',
            'items.invoiceItem',
            'invoice',
            'supplier',
            'branch',
            'admin',
            'treasury',
        ]);

        return view('livewire.purchase-returns.purchase-return-show', [
            'return' => $return,
        ]);
    }
}
