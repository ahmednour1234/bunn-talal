<?php

namespace App\Livewire\SaleOrders;

use App\Models\Treasury;
use App\Services\SaleOrderService;
use Livewire\Component;

class SaleOrderShow extends Component
{
    public int $orderId;
    public string $paymentAmount = '';
    public ?int $paymentTreasuryId = null;
    public string $paymentNotes = '';
    public bool $showPaymentForm = false;

    public function mount(int $id)
    {
        $this->orderId = $id;
    }

    public function togglePaymentForm()
    {
        $this->showPaymentForm = !$this->showPaymentForm;
    }

    public function addPayment(SaleOrderService $service)
    {
        $this->validate([
            'paymentAmount'     => 'required|numeric|min:0.01',
            'paymentTreasuryId' => 'required|exists:treasuries,id',
        ], [
            'paymentAmount.required'     => 'مبلغ الدفعة مطلوب',
            'paymentAmount.min'          => 'مبلغ الدفعة يجب أن يكون أكبر من صفر',
            'paymentTreasuryId.required' => 'الخزينة مطلوبة',
        ]);

        $admin = auth('admin')->user();

        try {
            $service->addPayment($this->orderId, [
                'amount'      => (float) $this->paymentAmount,
                'treasury_id' => $this->paymentTreasuryId,
                'admin_id'    => $admin->id,
                'notes'       => $this->paymentNotes ?: null,
            ]);

            $this->paymentAmount     = '';
            $this->paymentTreasuryId = null;
            $this->paymentNotes      = '';
            $this->showPaymentForm   = false;
            session()->flash('success', 'تم إضافة الدفعة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelOrder(SaleOrderService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('sale-orders.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }

        try {
            $service->cancelOrder($this->orderId);
            session()->flash('success', 'تم إلغاء الطلب بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(SaleOrderService $service)
    {
        $order = $service->getById($this->orderId);
        $order->load(['items.product', 'items.unit', 'payments.treasury', 'payments.admin', 'customer', 'branch', 'delegate', 'admin', 'treasury', 'returns']);

        return view('livewire.sale-orders.sale-order-show', [
            'order'      => $order,
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
