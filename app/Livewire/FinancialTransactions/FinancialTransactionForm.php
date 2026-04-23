<?php

namespace App\Livewire\FinancialTransactions;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\Treasury;
use App\Services\FinancialTransactionService;
use Livewire\Component;

class FinancialTransactionForm extends Component
{
    public ?int $transactionId = null;
    public string $type = 'expense';
    public ?int $account_id = null;
    public ?int $treasury_id = null;
    public string $amount = '';
    public string $description = '';
    public string $date = '';

    public function mount(FinancialTransactionService $transactionService, ?int $id = null)
    {
        $this->date = now()->format('Y-m-d');

        if ($id) {
            $this->transactionId = $id;
            $tx = $transactionService->getTransactionById($id);
            $this->type = $tx->type;
            $this->account_id = $tx->account_id;
            $this->treasury_id = $tx->treasury_id;
            $this->amount = (string) ($tx->amount * 1);
            $this->description = $tx->description ?? '';
            $this->date = $tx->date->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:expense,revenue',
            'account_id' => 'required|exists:accounts,id',
            'treasury_id' => 'nullable|exists:treasuries,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'type.required' => 'اختر النوع',
            'account_id.required' => 'اختر الحساب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'date.required' => 'التاريخ مطلوب',
        ];
    }

    public function save(FinancialTransactionService $transactionService)
    {
        $this->validate();

        $data = [
            'type' => $this->type,
            'account_id' => $this->account_id,
            'treasury_id' => $this->treasury_id ?: null,
            'amount' => $this->amount,
            'description' => $this->description ?: null,
            'date' => $this->date,
            'admin_id' => auth('admin')->id(),
        ];

        if ($this->transactionId) {
            $transactionService->updateTransaction($this->transactionId, $data);
            session()->flash('success', 'تم تحديث المعاملة بنجاح');
        } else {
            $transactionService->createTransaction($data);
            session()->flash('success', 'تم إضافة المعاملة بنجاح');
        }

        return redirect()->route('financial-transactions.index');
    }

    public function render()
    {
        return view('livewire.financial-transactions.financial-transaction-form', [
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
            'typeLabels' => FinancialTransaction::typeLabels(),
        ]);
    }
}
