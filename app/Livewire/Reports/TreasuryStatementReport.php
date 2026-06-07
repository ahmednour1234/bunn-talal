<?php

namespace App\Livewire\Reports;

use App\Models\PurchaseInvoicePayment;
use App\Models\PurchaseReturn;
use App\Models\SaleOrderPayment;
use App\Models\SaleReturn;
use App\Models\Treasury;
use App\Models\Trip;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Collection;
use Livewire\Component;

class TreasuryStatementReport extends Component
{
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $treasuryId = '';

    public function mount(): void
    {
        $this->dateFrom   = request('date_from', now()->startOfMonth()->format('Y-m-d'));
        $this->dateTo     = request('date_to',   now()->format('Y-m-d'));
        $this->treasuryId = request('treasury_id', '');
    }

    public function render()
    {
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();
        $treasuries = Treasury::where('is_active', true)->visibleToBranch($scopedBranchId)->orderBy('name')->get();
        // Treasury ids this admin may see; rows for other treasuries are filtered out below.
        $visibleTreasuryIds = $scopedBranchId ? $treasuries->pluck('id')->all() : null;

        // ── Sale order payments (deposits into treasury from customers) ──
        $salePayments = SaleOrderPayment::query()
            ->with('order.customer')
            ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
            ->when($this->dateFrom,   fn($q) => $q->where('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->where('payment_date', '<=', $this->dateTo))
            ->whereNotNull('treasury_id')
            ->get()
            ->map(fn($p) => [
                'date'        => $p->payment_date,
                'type'        => 'deposit',
                'amount'      => (float) $p->amount,
                'source'      => 'مبيعات',
                'description' => 'تحصيل طلب #' . $p->sale_order_id . ($p->order?->customer ? ' - ' . $p->order->customer->name : ''),
                'treasury_id' => $p->treasury_id,
                'ref'         => 'SO-' . $p->sale_order_id,
            ]);

        // ── Purchase invoice payments (withdrawals from treasury to suppliers) ──
        $purchasePayments = PurchaseInvoicePayment::query()
            ->with('invoice.supplier')
            ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
            ->when($this->dateFrom,   fn($q) => $q->where('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->where('payment_date', '<=', $this->dateTo))
            ->whereNotNull('treasury_id')
            ->get()
            ->map(fn($p) => [
                'date'        => $p->payment_date,
                'type'        => 'withdrawal',
                'amount'      => (float) $p->amount,
                'source'      => 'مشتريات',
                'description' => 'سداد فاتورة #' . $p->purchase_invoice_id . ($p->invoice?->supplier ? ' - ' . $p->invoice->supplier->name : ''),
                'treasury_id' => $p->treasury_id,
                'ref'         => 'PO-' . $p->purchase_invoice_id,
            ]);

        // ── Trip settlements (deposits into treasury) ──
        $tripSettlements = Trip::query()
            ->where('status', 'settled')
            ->where('settlement_cash_actual', '>', 0)
            ->when($this->treasuryId, fn($q) => $q->where('settlement_treasury_id', $this->treasuryId))
            ->when($this->dateFrom,   fn($q) => $q->whereDate('settled_at', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->whereDate('settled_at', '<=', $this->dateTo))
            ->whereNotNull('settlement_treasury_id')
            ->with('delegate')
            ->get()
            ->map(fn($t) => [
                'date'        => $t->settled_at?->toDateString() ?? $t->actual_return_date,
                'type'        => 'deposit',
                'amount'      => (float) $t->settlement_cash_actual,
                'source'      => 'تسوية رحلات',
                'description' => 'تسوية رحلة ' . $t->trip_number . ($t->delegate ? ' - ' . $t->delegate->name : ''),
                'treasury_id' => $t->settlement_treasury_id,
                'ref'         => $t->trip_number,
            ]);

        // ── Sale returns (withdrawals from treasury as refunds to customers) ──
        $saleReturns = SaleReturn::query()
            ->whereIn('status', ['refunded'])
            ->where('refund_amount', '>', 0)
            ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
            ->when($this->dateFrom,   fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->where('date', '<=', $this->dateTo))
            ->whereNotNull('treasury_id')
            ->with('customer')
            ->get()
            ->map(fn($r) => [
                'date'        => $r->date,
                'type'        => 'withdrawal',
                'amount'      => (float) $r->refund_amount,
                'source'      => 'مرتجع مبيعات',
                'description' => 'مرتجع مبيعات #' . $r->id . ($r->customer ? ' - ' . $r->customer->name : ''),
                'treasury_id' => $r->treasury_id,
                'ref'         => 'SR-' . $r->id,
            ]);

        // ── Purchase returns (deposits into treasury as refunds from suppliers) ──
        $purchaseReturns = PurchaseReturn::query()
            ->whereIn('status', ['refunded'])
            ->where('refund_amount', '>', 0)
            ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
            ->when($this->dateFrom,   fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->where('date', '<=', $this->dateTo))
            ->whereNotNull('treasury_id')
            ->with('supplier')
            ->get()
            ->map(fn($r) => [
                'date'        => $r->date,
                'type'        => 'deposit',
                'amount'      => (float) $r->refund_amount,
                'source'      => 'مرتجع مشتريات',
                'description' => 'مرتجع مشتريات #' . $r->id . ($r->supplier ? ' - ' . $r->supplier->name : ''),
                'treasury_id' => $r->treasury_id,
                'ref'         => 'PR-' . $r->id,
            ]);

        // ── Manual/Opening treasury transactions only (to avoid double-counting with source tables) ──
        // Only include records with OPENING or MANUAL-ADJ reference, or any manual transaction
        // NOT auto-generated by services (i.e., the ones with OPENING/MANUAL-ADJ ref)
        $manualTx = TreasuryTransaction::query()
            ->with(['treasury', 'admin'])
            ->whereIn('reference_number', ['OPENING', 'MANUAL-ADJ'])
            ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
            ->when($this->dateFrom,   fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->where('date', '<=', $this->dateTo))
            ->get()
            ->map(fn($t) => [
                'date'        => $t->date,
                'type'        => $t->type,
                'amount'      => (float) $t->amount,
                'source'      => $t->reference_number === 'OPENING' ? 'رصيد افتتاحي' : 'تعديل يدوي',
                'description' => $t->description ?? '—',
                'treasury_id' => $t->treasury_id,
                'ref'         => $t->reference_number ?? '—',
            ]);

        // ── Merge & sort all rows ──
        $allRows = collect()
            ->concat($salePayments)
            ->concat($purchasePayments)
            ->concat($tripSettlements)
            ->concat($saleReturns)
            ->concat($purchaseReturns)
            ->concat($manualTx)
            ->when($visibleTreasuryIds !== null, fn($rows) => $rows->whereIn('treasury_id', $visibleTreasuryIds))
            ->sortBy('date')
            ->values();

        // ── Totals ──
        $totalDeposits    = $allRows->where('type', 'deposit')->sum('amount');
        $totalWithdrawals = $allRows->where('type', 'withdrawal')->sum('amount');

        // ── Untracked balance (all-time) per treasury ──
        // Define treasuryBalances first so we can loop over it below
        $treasuryBalances = $this->treasuryId
            ? $treasuries->where('id', $this->treasuryId)
            : $treasuries;

        // Compute across ALL time (no date filter) to compare to current balance
        $allTimeSources = collect()
            ->concat(SaleOrderPayment::whereNotNull('treasury_id')
                ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
                ->get()->map(fn($p) => ['type' => 'deposit', 'amount' => (float)$p->amount, 'treasury_id' => $p->treasury_id]))
            ->concat(\App\Models\PurchaseInvoicePayment::whereNotNull('treasury_id')
                ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
                ->get()->map(fn($p) => ['type' => 'withdrawal', 'amount' => (float)$p->amount, 'treasury_id' => $p->treasury_id]))
            ->concat(Trip::where('status', 'settled')->where('settlement_cash_actual', '>', 0)->whereNotNull('settlement_treasury_id')
                ->when($this->treasuryId, fn($q) => $q->where('settlement_treasury_id', $this->treasuryId))
                ->get()->map(fn($t) => ['type' => 'deposit', 'amount' => (float)$t->settlement_cash_actual, 'treasury_id' => $t->settlement_treasury_id]))
            ->concat(\App\Models\SaleReturn::whereIn('status', ['refunded'])->where('refund_amount', '>', 0)->whereNotNull('treasury_id')
                ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
                ->get()->map(fn($r) => ['type' => 'withdrawal', 'amount' => (float)$r->refund_amount, 'treasury_id' => $r->treasury_id]))
            ->concat(\App\Models\PurchaseReturn::whereIn('status', ['refunded'])->where('refund_amount', '>', 0)->whereNotNull('treasury_id')
                ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
                ->get()->map(fn($r) => ['type' => 'deposit', 'amount' => (float)$r->refund_amount, 'treasury_id' => $r->treasury_id]))
            ->concat(TreasuryTransaction::whereIn('reference_number', ['OPENING', 'MANUAL-ADJ'])
                ->when($this->treasuryId, fn($q) => $q->where('treasury_id', $this->treasuryId))
                ->get()->map(fn($t) => ['type' => $t->type, 'amount' => (float)$t->amount, 'treasury_id' => $t->treasury_id]));

        if ($visibleTreasuryIds !== null) {
            $allTimeSources = $allTimeSources->whereIn('treasury_id', $visibleTreasuryIds);
        }

        // Calculate untracked balance per treasury
        $untrackedBalances = [];
        foreach ($treasuryBalances as $t) {
            $trackedDeposits    = $allTimeSources->where('treasury_id', $t->id)->where('type', 'deposit')->sum('amount');
            $trackedWithdrawals = $allTimeSources->where('treasury_id', $t->id)->where('type', 'withdrawal')->sum('amount');
            $trackedNet         = $trackedDeposits - $trackedWithdrawals;
            $untracked          = round((float)$t->balance - $trackedNet, 2);
            if ($untracked != 0) {
                $untrackedBalances[$t->id] = $untracked;
            }
        }

        // ── Summary by source ──
        $bySource = $allRows->groupBy('source')->map(fn($rows, $source) => [
            'source'      => $source,
            'deposits'    => $rows->where('type', 'deposit')->sum('amount'),
            'withdrawals' => $rows->where('type', 'withdrawal')->sum('amount'),
            'count'       => $rows->count(),
        ])->values();

        return view('livewire.reports.treasury-statement', compact(
            'treasuries',
            'treasuryBalances',
            'allRows',
            'totalDeposits',
            'totalWithdrawals',
            'bySource',
            'untrackedBalances',
        ));
    }
}
