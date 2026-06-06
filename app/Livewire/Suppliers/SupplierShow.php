<?php

namespace App\Livewire\Suppliers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoicePayment;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupplierShow extends Component
{
    public int $supplierId;
    public string $activeTab = 'overview';

    // Payment modal (pay supplier from treasury)
    public bool $showPaymentModal = false;
    public string $paymentAmount = '';
    public ?int $paymentTreasuryId = null;
    public ?int $paymentInvoiceId = null;
    public string $paymentDate = '';
    public string $paymentNotes = '';

    public function mount(int $id): void
    {
        $this->supplierId = $id;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function openPaymentModal(): void
    {
        $this->paymentAmount     = '';
        $this->paymentTreasuryId = null;
        $this->paymentInvoiceId  = null;
        $this->paymentDate       = now()->format('Y-m-d');
        $this->paymentNotes      = '';
        $this->showPaymentModal  = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function savePayment(): void
    {
        $this->validate([
            'paymentAmount'     => 'required|numeric|min:0.01',
            'paymentDate'       => 'required|date',
            'paymentTreasuryId' => 'nullable|exists:treasuries,id',
            'paymentInvoiceId'  => 'nullable|exists:purchase_invoices,id',
            'paymentNotes'      => 'nullable|string|max:500',
        ], [
            'paymentAmount.required' => 'المبلغ مطلوب',
            'paymentAmount.numeric'  => 'المبلغ يجب أن يكون رقماً',
            'paymentAmount.min'      => 'المبلغ يجب أن يكون أكبر من صفر',
            'paymentDate.required'   => 'التاريخ مطلوب',
            'paymentDate.date'       => 'التاريخ غير صحيح',
        ]);

        DB::transaction(function () {
            $amount = (float) $this->paymentAmount;

            // Record payment on the invoice if specified
            if ($this->paymentInvoiceId) {
                PurchaseInvoicePayment::create([
                    'purchase_invoice_id' => $this->paymentInvoiceId,
                    'amount'              => $amount,
                    'payment_date'        => $this->paymentDate,
                    'treasury_id'         => $this->paymentTreasuryId ?: null,
                    'payment_method'      => 'cash',
                    'admin_id'            => auth('admin')->id(),
                    'notes'               => $this->paymentNotes ?: null,
                ]);

                $invoice = PurchaseInvoice::find($this->paymentInvoiceId);
                if ($invoice) {
                    $invoice->increment('paid_amount', $amount);
                    $newPaid = (float) $invoice->fresh()->paid_amount;
                    $total   = (float) $invoice->total;
                    if ($newPaid >= $total) {
                        $invoice->update(['status' => 'paid']);
                    } elseif ($newPaid > 0) {
                        $invoice->update(['status' => 'partial_paid']);
                    }
                }
            }

            // Reduce supplier balance (we paid them, reduces our debt)
            Supplier::where('id', $this->supplierId)->decrement('balance', $amount);

            // Withdraw from treasury
            if ($this->paymentTreasuryId) {
                Treasury::where('id', $this->paymentTreasuryId)->decrement('balance', $amount);

                TreasuryTransaction::create([
                    'treasury_id'      => $this->paymentTreasuryId,
                    'type'             => 'withdrawal',
                    'amount'           => $amount,
                    'description'      => 'دفعة للمورد - ' . Supplier::find($this->supplierId)?->name,
                    'reference_number' => 'SUP-PAY-' . $this->supplierId,
                    'date'             => $this->paymentDate,
                    'admin_id'         => auth('admin')->id(),
                ]);
            }
        });

        $this->showPaymentModal = false;
        session()->flash('success', 'تم تسجيل الدفعة للمورد بنجاح');
    }

    public function render()
    {
        $supplier = Supplier::findOrFail($this->supplierId);

        // ── Purchase Invoices ─────────────────────────────────────
        $invoices = PurchaseInvoice::where('supplier_id', $this->supplierId)
            ->with('branch', 'payments')
            ->orderByDesc('date')
            ->get();

        $totalInvoiced  = $invoices->whereNotIn('status', ['cancelled', 'draft'])->sum('total');
        $totalPaid      = $invoices->whereNotIn('status', ['cancelled', 'draft'])->sum('paid_amount');
        $totalRemaining = $invoices->whereIn('status', ['confirmed', 'partial_paid'])->sum(fn ($i) => (float) $i->total - (float) $i->paid_amount);

        // ── Purchase Returns ──────────────────────────────────────
        $returns = PurchaseReturn::where('supplier_id', $this->supplierId)
            ->with('branch', 'invoice')
            ->orderByDesc('date')
            ->get();

        $totalReturns = $returns->whereIn('status', ['confirmed', 'refunded'])->sum('refund_amount');

        $returnsByInvoice = $returns->whereIn('status', ['confirmed', 'refunded'])
            ->groupBy('purchase_invoice_id');

        // ── Payments made to supplier ─────────────────────────────
        $payments = PurchaseInvoicePayment::whereHas(
            'invoice',
            fn ($q) => $q->where('supplier_id', $this->supplierId)
        )
            ->with('treasury', 'invoice')
            ->orderByDesc('payment_date')
            ->get();

        $totalPayments = $payments->sum('amount');

        // ── KPI ───────────────────────────────────────────────────
        $currentBalance = (float) $supplier->balance;

        // ── Account Statement Ledger ──────────────────────────────
        $ledger = collect();

        foreach ($invoices->whereNotIn('status', ['draft']) as $inv) {
            $isCancelled = $inv->status === 'cancelled';

            $ledger->push([
                'date'        => $inv->date,
                'type'        => 'invoice',
                'reference'   => $inv->invoice_number,
                'description' => 'فاتورة مشتريات',
                'debit'       => $isCancelled ? 0 : (float) $inv->total,
                'credit'      => 0,
                'cancelled'   => $isCancelled,
            ]);

            if (!$isCancelled && (float) $inv->paid_amount > 0) {
                $ledger->push([
                    'date'        => $inv->date,
                    'type'        => 'payment',
                    'reference'   => $inv->invoice_number,
                    'description' => 'دفعة على فاتورة',
                    'debit'       => 0,
                    'credit'      => (float) $inv->paid_amount,
                    'cancelled'   => false,
                ]);
            }

            if ($isCancelled) {
                $ledger->push([
                    'date'        => $inv->date,
                    'type'        => 'cancellation',
                    'reference'   => $inv->invoice_number,
                    'description' => 'إلغاء فاتورة مشتريات',
                    'debit'       => 0,
                    'credit'      => (float) $inv->total,
                    'cancelled'   => true,
                ]);
            }
        }

        foreach ($returns->whereIn('status', ['confirmed', 'refunded']) as $ret) {
            $ledger->push([
                'date'        => $ret->date,
                'type'        => 'return',
                'reference'   => $ret->return_number,
                'description' => 'مرتجع مشتريات',
                'debit'       => 0,
                'credit'      => (float) $ret->refund_amount,
                'cancelled'   => false,
            ]);
        }

        // Sort ledger by date then type
        $ledger = $ledger->sortBy([['date', 'asc'], ['type', 'asc']])->values();

        // Running balance
        $running        = (float) $supplier->opening_balance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $ledgerWithBalance = $ledger->map(function ($row) use (&$running, &$totalDebit, &$totalCredit) {
            if (!$row['cancelled']) {
                $running     += $row['debit'] - $row['credit'];
                $totalDebit  += $row['debit'];
                $totalCredit += $row['credit'];
            }
            $row['running_balance'] = $running;
            return $row;
        });

        $treasuries = Treasury::where('is_active', true)->orderBy('name')->get();

        return view('livewire.suppliers.supplier-show', [
            'supplier'          => $supplier,
            'invoices'          => $invoices,
            'returns'           => $returns,
            'returnsByInvoice'  => $returnsByInvoice,
            'payments'          => $payments,
            'totalInvoiced'     => $totalInvoiced,
            'totalPaid'         => $totalPaid,
            'totalRemaining'    => $totalRemaining,
            'totalReturns'      => $totalReturns,
            'totalPayments'     => $totalPayments,
            'currentBalance'    => $currentBalance,
            'ledger'            => $ledgerWithBalance,
            'totalDebit'        => $totalDebit,
            'totalCredit'       => $totalCredit,
            'runningBalance'    => $running,
            'treasuries'        => $treasuries,
        ]);
    }
}
