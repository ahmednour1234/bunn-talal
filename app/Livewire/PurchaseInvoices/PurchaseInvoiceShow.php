<?php

namespace App\Livewire\PurchaseInvoices;

use App\Models\Treasury;
use App\Services\PurchaseInvoiceService;
use Livewire\Component;

class PurchaseInvoiceShow extends Component
{
    public int $invoiceId;
    public string $paymentAmount = '';
    public ?int $paymentTreasuryId = null;
    public string $paymentNotes = '';
    public bool $showPaymentForm = false;

    public function mount(int $id)
    {
        $this->invoiceId = $id;
    }

    public function togglePaymentForm()
    {
        $this->showPaymentForm = !$this->showPaymentForm;
    }

    public function addPayment(PurchaseInvoiceService $service)
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentTreasuryId' => 'required|exists:treasuries,id',
        ], [
            'paymentAmount.required' => 'مبلغ الدفعة مطلوب',
            'paymentAmount.min' => 'مبلغ الدفعة يجب أن يكون أكبر من صفر',
            'paymentTreasuryId.required' => 'الخزينة مطلوبة',
        ]);

        $admin = auth('admin')->user();

        try {
            $service->addPayment($this->invoiceId, [
                'amount' => (float) $this->paymentAmount,
                'treasury_id' => $this->paymentTreasuryId,
                'admin_id' => $admin->id,
                'notes' => $this->paymentNotes ?: null,
            ]);

            $this->paymentAmount = '';
            $this->paymentTreasuryId = null;
            $this->paymentNotes = '';
            $this->showPaymentForm = false;
            session()->flash('success', 'تم إضافة الدفعة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelInvoice(PurchaseInvoiceService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('purchase-invoices.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }

        try {
            $service->cancelInvoice($this->invoiceId);
            session()->flash('success', 'تم إلغاء الفاتورة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(PurchaseInvoiceService $service)
    {
        $invoice = $service->getById($this->invoiceId);
        $invoice->load(['items.product', 'items.unit', 'payments.treasury', 'payments.admin', 'supplier', 'branch', 'admin', 'treasury']);

        return view('livewire.purchase-invoices.purchase-invoice-show', [
            'invoice' => $invoice,
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
