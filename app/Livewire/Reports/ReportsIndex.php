<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Livewire\Component;

class ReportsIndex extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $branchId = auth('admin')->user()?->scopedBranchId();

        $totalTreasuryBalance = Treasury::where('is_active', true)->visibleToBranch($branchId)->sum('balance');

        $financialQuery = FinancialTransaction::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $treasuryTxQuery = TreasuryTransaction::query()
            ->when($branchId, fn($q) => $q->whereHas('treasury', fn($t) => $t->visibleToBranch($branchId)));

        if ($this->dateFrom) {
            $financialQuery->where('date', '>=', $this->dateFrom);
            $treasuryTxQuery->where('date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $financialQuery->where('date', '<=', $this->dateTo);
            $treasuryTxQuery->where('date', '<=', $this->dateTo);
        }

        $totalExpenses = (clone $financialQuery)->where('type', 'expense')->sum('amount');
        $totalRevenues = (clone $financialQuery)->where('type', 'revenue')->sum('amount');

        $totalDeposits = (clone $treasuryTxQuery)->where('type', 'deposit')->sum('amount');
        $totalWithdrawals = (clone $treasuryTxQuery)->where('type', 'withdrawal')->sum('amount');

        $expensesByAccount = FinancialTransaction::query()
            ->where('type', 'expense')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($this->dateFrom, fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('date', '<=', $this->dateTo))
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();

        $revenuesByAccount = FinancialTransaction::query()
            ->where('type', 'revenue')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($this->dateFrom, fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('date', '<=', $this->dateTo))
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();

        $treasuryBalances = Treasury::where('is_active', true)->visibleToBranch($branchId)->orderBy('name')->get();

        $recentTransactions = FinancialTransaction::with(['account', 'admin'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($this->dateFrom, fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('date', '<=', $this->dateTo))
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.reports.reports-index', compact(
            'totalTreasuryBalance',
            'totalExpenses',
            'totalRevenues',
            'totalDeposits',
            'totalWithdrawals',
            'expensesByAccount',
            'revenuesByAccount',
            'treasuryBalances',
            'recentTransactions',
        ));
    }
}
