<?php

namespace App\Livewire\TreasuryTransactions;

use App\Models\Treasury;
use App\Services\TreasuryTransactionService;
use Livewire\Component;

class TreasuryTransferForm extends Component
{
    public ?int $from_treasury_id = null;
    public ?int $to_treasury_id   = null;
    public string $amount         = '';
    public string $description    = '';
    public string $date           = '';
    public string $reference_number = '';

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'from_treasury_id' => 'required|exists:treasuries,id|different:to_treasury_id',
            'to_treasury_id'   => 'required|exists:treasuries,id|different:from_treasury_id',
            'amount'           => 'required|numeric|min:0.01',
            'date'             => 'required|date',
            'description'      => 'nullable|string|max:1000',
            'reference_number' => 'nullable|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'from_treasury_id.required'  => 'اختر خزنة المصدر',
            'from_treasury_id.different' => 'يجب أن تكون الخزنتان مختلفتين',
            'to_treasury_id.required'    => 'اختر خزنة الوجهة',
            'to_treasury_id.different'   => 'يجب أن تكون الخزنتان مختلفتين',
            'amount.required'            => 'المبلغ مطلوب',
            'amount.min'                 => 'المبلغ يجب أن يكون أكبر من صفر',
            'date.required'              => 'التاريخ مطلوب',
        ];
    }

    public function save(TreasuryTransactionService $service): mixed
    {
        $this->validate();

        try {
            $service->transferBetweenTreasuries([
                'from_treasury_id' => $this->from_treasury_id,
                'to_treasury_id'   => $this->to_treasury_id,
                'amount'           => $this->amount,
                'description'      => $this->description ?: null,
                'date'             => $this->date,
                'reference_number' => $this->reference_number ?: null,
                'admin_id'         => auth('admin')->id(),
            ]);
        } catch (\Exception $e) {
            $this->addError('amount', $e->getMessage());
            return null;
        }

        session()->flash('success', 'تم تنفيذ التحويل بنجاح');

        return redirect()->route('treasury-transactions.index');
    }

    public function render()
    {
        $fromTreasury = $this->from_treasury_id
            ? Treasury::find($this->from_treasury_id)
            : null;

        return view('livewire.treasury-transactions.treasury-transfer-form', [
            'treasuries'   => Treasury::where('is_active', true)
                ->visibleToBranch(auth('admin')->user()?->scopedBranchId())
                ->orderBy('name')->get(),
            'fromTreasury' => $fromTreasury,
        ]);
    }
}
