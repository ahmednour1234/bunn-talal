<?php

namespace App\Livewire\TreasuryTransactions;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Services\TreasuryTransactionService;
use Livewire\Component;

class TreasuryTransactionForm extends Component
{
    public ?int $treasury_id = null;
    public string $type = 'deposit';
    public string $amount = '';
    public string $description = '';
    public string $date = '';
    public string $reference_number = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'treasury_id' => 'required|exists:treasuries,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'reference_number' => 'nullable|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'treasury_id.required' => 'اختر الخزنة',
            'type.required' => 'اختر نوع العملية',
            'amount.required' => 'المبلغ مطلوب',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'date.required' => 'التاريخ مطلوب',
        ];
    }

    public function save(TreasuryTransactionService $transactionService)
    {
        $this->validate();

        $data = [
            'treasury_id' => $this->treasury_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description ?: null,
            'date' => $this->date,
            'reference_number' => $this->reference_number ?: null,
            'admin_id' => auth('admin')->id(),
        ];

        $transactionService->createTransaction($data);

        $label = $this->type === 'deposit' ? 'إيداع' : 'سحب';
        session()->flash('success', "تم تسجيل عملية {$label} بنجاح");

        return redirect()->route('treasury-transactions.index');
    }

    public function render()
    {
        return view('livewire.treasury-transactions.treasury-transaction-form', [
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
            'typeLabels' => TreasuryTransaction::typeLabels(),
        ]);
    }
}
