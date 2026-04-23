<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\FinancialTransaction;
use Livewire\Component;

class AccountStatementReport extends Component
{
    public string $accountId = '';
    public string $dateFrom  = '';
    public string $dateTo    = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        $transactions = collect();
        $selectedAccount = null;
        $runningBalance  = 0;
        $totalDebit      = 0;
        $totalCredit     = 0;

        if ($this->accountId) {
            $selectedAccount = Account::find((int) $this->accountId);

            $query = FinancialTransaction::where('account_id', (int) $this->accountId)
                ->with('admin', 'treasury')
                ->orderBy('date')
                ->orderBy('id');

            if ($this->dateFrom) {
                $query->where('date', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $query->where('date', '<=', $this->dateTo);
            }

            $rawTxs = $query->get();

            foreach ($rawTxs as $tx) {
                // revenue = credit (increases income), expense = debit (increases costs)
                $debit  = $tx->type === 'expense' ? (float) $tx->amount : 0;
                $credit = $tx->type === 'revenue' ? (float) $tx->amount : 0;
                $runningBalance += $credit - $debit;
                $totalDebit  += $debit;
                $totalCredit += $credit;

                $transactions->push([
                    'id'          => $tx->id,
                    'date'        => $tx->date,
                    'type'        => $tx->type,
                    'type_label'  => $tx->type_label,
                    'description' => $tx->description,
                    'treasury'    => $tx->treasury?->name,
                    'admin'       => $tx->admin?->name,
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ]);
            }
        }

        return view('livewire.reports.account-statement', compact(
            'accounts', 'selectedAccount', 'transactions',
            'totalDebit', 'totalCredit', 'runningBalance',
        ));
    }
}
