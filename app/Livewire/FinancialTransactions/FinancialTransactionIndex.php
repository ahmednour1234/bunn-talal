<?php

namespace App\Livewire\FinancialTransactions;

use App\Models\Account;
use App\Models\Branch;
use App\Models\FinancialTransaction;
use App\Services\FinancialTransactionService;
use Livewire\Component;
use Livewire\WithPagination;

class FinancialTransactionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $accountFilter = '';
    public string $branchFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingAccountFilter()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function delete(int $id, FinancialTransactionService $transactionService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('financial-transactions.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $transactionService->deleteTransaction($id);
        session()->flash('success', 'تم حذف المعاملة بنجاح');
    }

    public function render()
    {
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();
        if ($scopedBranchId) {
            $this->branchFilter = (string) $scopedBranchId;
        }

        $query = FinancialTransaction::query()->with(['account', 'treasury', 'branch', 'admin']);

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->accountFilter) {
            $query->where('account_id', $this->accountFilter);
        }

        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        return view('livewire.financial-transactions.financial-transaction-index', [
            'transactions' => $query->latest()->paginate(10),
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'typeLabels' => FinancialTransaction::typeLabels(),
            'scopedBranchId' => $scopedBranchId,
        ]);
    }
}
