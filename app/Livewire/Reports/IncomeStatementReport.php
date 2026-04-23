<?php

namespace App\Livewire\Reports;

use App\Models\FinancialTransaction;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use Livewire\Component;

class IncomeStatementReport extends Component
{
    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $period   = 'month'; // month, quarter, year, custom

    public function mount(): void
    {
        $this->applyPeriod('month');
    }

    public function applyPeriod(string $period): void
    {
        $this->period = $period;
        match ($period) {
            'month'   => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'quarter' => [$this->dateFrom, $this->dateTo] = [now()->startOfQuarter()->format('Y-m-d'), now()->endOfQuarter()->format('Y-m-d')],
            'year'    => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default   => null,
        };
    }

    public function updatedPeriod(string $value): void
    {
        if ($value !== 'custom') {
            $this->applyPeriod($value);
        }
    }

    public function render()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to   = $this->dateTo   ?: now()->format('Y-m-d');

        // ── REVENUES ─────────────────────────────────────────────────

        // 1. Sales Revenue (confirmed/paid/partial_paid sale orders)
        $salesRevenue = SaleOrder::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('date', [$from, $to])
            ->sum('total');

        // 2. Sales Returns (deducted from revenue)
        $salesReturns = SaleReturn::where('status', 'completed')
            ->whereBetween('date', [$from, $to])
            ->sum('refund_amount');

        // 3. Other revenues from financial transactions
        $otherRevenues = FinancialTransaction::where('type', 'revenue')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();
        $otherRevenuesTotal = $otherRevenues->sum('total');

        $netSalesRevenue = $salesRevenue - $salesReturns;
        $totalRevenue    = $netSalesRevenue + $otherRevenuesTotal;

        // ── COST OF GOODS SOLD ────────────────────────────────────────
        $purchaseCost = PurchaseInvoice::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('date', [$from, $to])
            ->sum('total');

        $purchaseReturns = PurchaseReturn::where('status', 'completed')
            ->whereBetween('date', [$from, $to])
            ->sum('refund_amount');

        $netCogs = $purchaseCost - $purchaseReturns;

        // ── GROSS PROFIT ─────────────────────────────────────────────
        $grossProfit = $totalRevenue - $netCogs;

        // ── EXPENSES ─────────────────────────────────────────────────
        $expenseLines = FinancialTransaction::where('type', 'expense')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();
        $totalExpenses = $expenseLines->sum('total');

        // ── NET PROFIT ────────────────────────────────────────────────
        $netProfit = $grossProfit - $totalExpenses;

        // ── Monthly breakdown for chart ───────────────────────────────
        $monthlyRevenue = SaleOrder::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('date', [$from, $to])
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthlyExpenses = FinancialTransaction::where('type', 'expense')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $allMonths = $monthlyRevenue->keys()->merge($monthlyExpenses->keys())->unique()->sort()->values();
        $chartMonths    = $allMonths->toArray();
        $chartRevenue   = $allMonths->map(fn($m) => round((float)($monthlyRevenue[$m] ?? 0), 2))->toArray();
        $chartExpenses  = $allMonths->map(fn($m) => round((float)($monthlyExpenses[$m] ?? 0), 2))->toArray();

        return view('livewire.reports.income-statement', compact(
            'salesRevenue', 'salesReturns', 'netSalesRevenue',
            'otherRevenues', 'otherRevenuesTotal', 'totalRevenue',
            'purchaseCost', 'purchaseReturns', 'netCogs',
            'grossProfit',
            'expenseLines', 'totalExpenses',
            'netProfit',
            'chartMonths', 'chartRevenue', 'chartExpenses',
        ));
    }
}
