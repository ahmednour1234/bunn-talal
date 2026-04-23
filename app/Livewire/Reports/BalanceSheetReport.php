<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\InstallmentPlan;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use Livewire\Component;

class BalanceSheetReport extends Component
{
    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $asOf = $this->asOfDate ?: now()->format('Y-m-d');

        // ══ ASSETS ═══════════════════════════════════════════════════

        // 1. Current Assets

        // a) Cash & Treasuries
        $treasuries = Treasury::where('is_active', true)->orderBy('name')->get();
        $totalCash  = $treasuries->sum('balance');

        // b) Accounts Receivable (unpaid/partial sale orders)
        $receivables = SaleOrder::whereIn('status', ['confirmed', 'partial_paid'])
            ->where('date', '<=', $asOf)
            ->get()
            ->sum(fn($o) => (float)$o->total - (float)$o->paid_amount);

        // c) Installment Receivables (customer plans outstanding)
        $installmentReceivables = InstallmentPlan::where('party_type', 'customer')
            ->where('status', 'active')
            ->get()
            ->sum(fn($p) => $p->outstanding);

        // d) Inventory Value (qty × cost_price across all branches)
        $inventoryValue = Product::with('branches')->get()->sum(function ($p) {
            $qty = $p->branches->sum('pivot.quantity');
            return $qty * (float) $p->cost_price;
        });

        $totalCurrentAssets = $totalCash + $receivables + $installmentReceivables + $inventoryValue;

        // 2. Non-current Assets (none tracked — placeholder)
        $totalNonCurrentAssets = 0;

        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        // ══ LIABILITIES ══════════════════════════════════════════════

        // 1. Current Liabilities

        // a) Accounts Payable (unpaid/partial purchase invoices)
        $payables = PurchaseInvoice::whereIn('status', ['confirmed', 'partial_paid'])
            ->where('date', '<=', $asOf)
            ->get()
            ->sum(fn($i) => (float)$i->total - (float)$i->paid_amount);

        // b) Installment Payables (supplier plans outstanding)
        $installmentPayables = InstallmentPlan::where('party_type', 'supplier')
            ->where('status', 'active')
            ->get()
            ->sum(fn($p) => $p->outstanding);

        // c) Sale Returns Payable (pending refunds to customers)
        $saleReturnsPending = SaleReturn::where('status', 'pending')
            ->where('date', '<=', $asOf)
            ->sum('refund_amount');

        $totalCurrentLiabilities = $payables + $installmentPayables + $saleReturnsPending;

        $totalLiabilities = $totalCurrentLiabilities;

        // ══ EQUITY ═══════════════════════════════════════════════════

        // Revenue accumulated
        $totalRevenue = SaleOrder::whereNotIn('status', ['draft', 'cancelled'])
            ->where('date', '<=', $asOf)
            ->sum('total');

        $totalSalesReturns = SaleReturn::where('status', 'completed')
            ->where('date', '<=', $asOf)
            ->sum('refund_amount');

        $otherRevenue = FinancialTransaction::where('type', 'revenue')
            ->where('date', '<=', $asOf)
            ->sum('amount');

        $totalExpenses = FinancialTransaction::where('type', 'expense')
            ->where('date', '<=', $asOf)
            ->sum('amount');

        $purchaseCosts = PurchaseInvoice::whereNotIn('status', ['draft', 'cancelled'])
            ->where('date', '<=', $asOf)
            ->sum('total');

        $netIncome = ($totalRevenue - $totalSalesReturns + $otherRevenue) - ($purchaseCosts + $totalExpenses);

        // Opening equity = assets - liabilities - netIncome (derived)
        $retainedEarnings = $netIncome;
        $equity           = $totalAssets - $totalLiabilities;

        // ══ Summary for display ═══════════════════════════════════════
        return view('livewire.reports.balance-sheet', compact(
            'asOf',
            'treasuries', 'totalCash',
            'receivables', 'installmentReceivables', 'inventoryValue',
            'totalCurrentAssets', 'totalNonCurrentAssets', 'totalAssets',
            'payables', 'installmentPayables', 'saleReturnsPending',
            'totalCurrentLiabilities', 'totalLiabilities',
            'netIncome', 'equity',
        ));
    }
}
