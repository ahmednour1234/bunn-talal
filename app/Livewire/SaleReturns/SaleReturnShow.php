<?php

namespace App\Livewire\SaleReturns;

use App\Services\SaleReturnService;
use Livewire\Component;

class SaleReturnShow extends Component
{
    public int $returnId;

    public function mount(int $id)
    {
        $this->returnId = $id;
    }

    public function confirmReturn(SaleReturnService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('sale-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->confirmReturn($this->returnId);
            session()->flash('success', 'تم تأكيد مرتجع المبيعات');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelReturn(SaleReturnService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('sale-returns.create')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->cancelReturn($this->returnId);
            session()->flash('success', 'تم إلغاء مرتجع المبيعات');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(SaleReturnService $service)
    {
        $return = $service->getById($this->returnId);
        $return->load([
            'items.product',
            'items.unit',
            'items.orderItem',
            'order',
            'customer',
            'branch',
            'admin',
            'treasury',
        ]);

        return view('livewire.sale-returns.sale-return-show', [
            'return' => $return,
        ]);
    }
}
