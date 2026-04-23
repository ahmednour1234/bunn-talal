<?php

namespace App\Livewire\Delegates;

use App\Models\Delegate;
use App\Models\DelegateLoan;
use App\Models\Treasury;
use Livewire\Component;

class DelegateShow extends Component
{
    public Delegate $delegate;
    public int $delegateId;
    public string $activeTab = 'overview';

    // Loan form
    public bool $showLoanForm = false;
    public string $loanAmount    = '';
    public string $loanDueDate   = '';
    public string $loanNote      = '';
    public ?int   $loanTreasuryId = null;

    // Pay loan modal
    public ?int  $payLoanId     = null;
    public string $payAmount    = '';

    public function mount(int $id): void
    {
        $this->delegateId = $id;
        $this->delegate   = Delegate::with([
            'branches', 'areas',
        ])->findOrFail($id);
    }

    public function saveLoan(): void
    {
        $this->validate([
            'loanAmount'    => 'required|numeric|min:0.01',
            'loanDueDate'   => 'nullable|date',
            'loanNote'      => 'nullable|string|max:500',
            'loanTreasuryId' => 'nullable|exists:treasuries,id',
        ]);

        DelegateLoan::create([
            'delegate_id' => $this->delegateId,
            'treasury_id' => $this->loanTreasuryId,
            'admin_id'    => auth('admin')->id(),
            'amount'      => $this->loanAmount,
            'paid_amount' => 0,
            'due_date'    => $this->loanDueDate ?: null,
            'note'        => $this->loanNote ?: null,
            'is_paid'     => false,
        ]);

        $this->reset('loanAmount', 'loanDueDate', 'loanNote', 'loanTreasuryId', 'showLoanForm');
        session()->flash('success', 'تم تسجيل السلفة بنجاح');
    }

    public function openPayModal(int $loanId): void
    {
        $loan = DelegateLoan::findOrFail($loanId);
        $this->payLoanId = $loanId;
        $this->payAmount  = (string) round($loan->remaining, 2);
    }

    public function payLoan(): void
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
        ]);

        $loan = DelegateLoan::findOrFail($this->payLoanId);
        $newPaid = min((float)$loan->amount, (float)$loan->paid_amount + (float)$this->payAmount);

        $loan->update([
            'paid_amount' => $newPaid,
            'is_paid'     => $newPaid >= (float)$loan->amount,
            'paid_at'     => $newPaid >= (float)$loan->amount ? now()->toDateString() : $loan->paid_at,
        ]);

        $this->reset('payLoanId', 'payAmount');
        session()->flash('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function render()
    {
        $trips = $this->delegate->trips()
            ->with('branch')
            ->latest('id')
            ->get();

        $loans = $this->delegate->loans()
            ->with('treasury')
            ->latest('id')
            ->get();

        $treasuries = Treasury::where('is_active', true)->orderBy('name')->get();

        // Performance summary
        $totalTrips     = $trips->count();
        $activeTrips    = $trips->where('status', 'active')->count();
        $settledTrips   = $trips->where('status', 'settled')->count();
        $totalInvoiced  = $trips->sum('total_invoiced');
        $totalCollected = $trips->sum('total_collected');
        $cashDeficit    = $trips->sum('settlement_cash_deficit');
        $productDeficit = $trips->sum('settlement_product_deficit');

        // Loans summary
        $totalLoans     = $loans->sum('amount');
        $totalLoanPaid  = $loans->sum('paid_amount');
        $totalLoanOwed  = $loans->sum(fn($l) => max(0, (float)$l->amount - (float)$l->paid_amount));
        $overdueLoans   = $loans->filter(fn($l) => !$l->is_paid && $l->due_date && $l->due_date->isPast());

        return view('livewire.delegates.delegate-show', compact(
            'trips', 'loans', 'treasuries',
            'totalTrips', 'activeTrips', 'settledTrips',
            'totalInvoiced', 'totalCollected', 'cashDeficit', 'productDeficit',
            'totalLoans', 'totalLoanPaid', 'totalLoanOwed', 'overdueLoans',
        ));
    }
}
