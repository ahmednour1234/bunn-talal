<?php

namespace App\Livewire\TreasuryTransactions;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class TreasuryTransactionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $treasuryFilter = '';
    public string $typeFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTreasuryFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = TreasuryTransaction::query()->with(['treasury', 'admin']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($this->treasuryFilter) {
            $query->where('treasury_id', $this->treasuryFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        return view('livewire.treasury-transactions.treasury-transaction-index', [
            'transactions' => $query->latest()->paginate(10),
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
            'typeLabels' => TreasuryTransaction::typeLabels(),
        ]);
    }
}
