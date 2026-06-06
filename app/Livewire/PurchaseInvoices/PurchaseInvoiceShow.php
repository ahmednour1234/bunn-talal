<?php

namespace App\Livewire\PurchaseInvoices;

use App\Models\PurchaseInvoicePayment;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Services\PurchaseInvoiceService;
use Illuminate\Support\Facades\DB;
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

    public function recalculateStatus(): void
    {
        DB::transaction(function () {
            $invoice = \App\Models\PurchaseInvoice::findOrFail($this->invoiceId);

            if (in_array($invoice->status, ['cancelled', 'draft'])) {
                return;
            }

            // Sum actual payments
            $paidViaPayments = PurchaseInvoicePayment::where('purchase_invoice_id', $this->invoiceId)
                ->sum('amount');

            // Sum confirmed/refunded returns applied to this invoice
            $paidViaReturns = PurchaseReturn::where('purchase_invoice_id', $this->invoiceId)
                ->whereIn('status', ['confirmed', 'refunded'])
                ->sum('refund_amount');

            $totalPaid = round((float) $paidViaPayments + (float) $paidViaReturns, 2);
            $total     = (float) $invoice->total;

            if ($totalPaid >= $total) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial_paid';
            } else {
                $status = 'confirmed';
            }

            $invoice->update([
                'paid_amount' => $totalPaid,
                'status'      => $status,
            ]);

            // Also sync supplier balance
            $supplierId = $invoice->supplier_id;
            if ($supplierId) {
                $allInvoicesTotal = \App\Models\PurchaseInvoice::where('supplier_id', $supplierId)
                    ->whereNotIn('status', ['cancelled', 'draft'])
                    ->sum('total');

                $allPayments = PurchaseInvoicePayment::whereHas(
                    'invoice',
                    fn ($q) => $q->where('supplier_id', $supplierId)
                )->sum('amount');

                $allReturns = PurchaseReturn::where('supplier_id', $supplierId)
                    ->whereIn('status', ['confirmed', 'refunded'])
                    ->sum('refund_amount');

                $supplier = \App\Models\Supplier::find($supplierId);
                if ($supplier) {
                    $correctBalance = (float) $supplier->opening_balance
                        + (float) $allInvoicesTotal
                        - (float) $allPayments
                        - (float) $allReturns;
                    $supplier->update(['balance' => round($correctBalance, 2)]);
                }
            }
        });

        session()->flash('success', 'تم إعادة حساب حالة الفاتورة ورصيد المورد بنجاح');
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
