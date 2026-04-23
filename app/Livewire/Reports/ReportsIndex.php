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
        $totalTreasuryBalance = Treasury::where('is_active', true)->sum('balance');

        $financialQuery = FinancialTransaction::query();
        $treasuryTxQuery = TreasuryTransaction::query();

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
            ->when($this->dateFrom, fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('date', '<=', $this->dateTo))
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();

        $revenuesByAccount = FinancialTransaction::query()
            ->where('type', 'revenue')
            ->when($this->dateFrom, fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('date', '<=', $this->dateTo))
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();

        $treasuryBalances = Treasury::where('is_active', true)->orderBy('name')->get();

        $recentTransactions = FinancialTransaction::with(['account', 'admin'])
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
