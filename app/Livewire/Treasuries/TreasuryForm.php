<?php

namespace App\Livewire\Treasuries;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Services\TreasuryService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TreasuryForm extends Component
{
    public ?int $treasuryId = null;
    public string $name = '';
    public string $balance = '0';
    public bool $is_active = true;

    /** الرصيد الأصلي قبل التعديل */
    private float $originalBalance = 0;

    public function mount(TreasuryService $treasuryService, ?int $id = null)
    {
        if ($id) {
            $this->treasuryId = $id;
            $treasury = $treasuryService->getTreasuryById($id);
            $this->name = $treasury->name;
            $this->balance = (string) ($treasury->balance * 1);
            $this->is_active = $treasury->is_active;
            $this->originalBalance = (float) $treasury->balance;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم الخزنة مطلوب',
            'balance.min' => 'الرصيد يجب أن يكون 0 أو أكثر',
        ];
    }

    public function save(TreasuryService $treasuryService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'balance' => $this->balance,
            'is_active' => $this->is_active,
        ];

        if ($this->treasuryId) {
            DB::transaction(function () use ($treasuryService, $data) {
                // Detect manual balance change and log a transaction
                $newBalance = (float) $this->balance;
                $diff = round($newBalance - $this->originalBalance, 4);

                if ($diff != 0) {
                    // Re-read the original balance from DB to avoid Livewire re-hydration issues
                    $currentBalance = (float) Treasury::find($this->treasuryId)?->balance;
                    $diff = round($newBalance - $currentBalance, 4);
                }

                $treasuryService->updateTreasury($this->treasuryId, $data);

                if ($diff != 0) {
                    TreasuryTransaction::create([
                        'treasury_id'      => $this->treasuryId,
                        'type'             => $diff > 0 ? 'deposit' : 'withdrawal',
                        'amount'           => abs($diff),
                        'date'             => now()->toDateString(),
                        'description'      => $diff > 0
                            ? 'تعديل يدوي — رصيد افتتاحي أو تسوية'
                            : 'تعديل يدوي — خصم رصيد',
                        'reference_number' => 'MANUAL-ADJ',
                        'admin_id'         => auth('admin')->id(),
                    ]);
                }
            });
            session()->flash('success', 'تم تحديث الخزنة بنجاح');
        } else {
            // New treasury: if opening balance > 0, log it
            DB::transaction(function () use ($treasuryService, $data) {
                $treasury = $treasuryService->createTreasury($data);
                if ((float) $this->balance > 0) {
                    TreasuryTransaction::create([
                        'treasury_id'      => $treasury->id,
                        'type'             => 'deposit',
                        'amount'           => (float) $this->balance,
                        'date'             => now()->toDateString(),
                        'description'      => 'رصيد افتتاحي',
                        'reference_number' => 'OPENING',
                        'admin_id'         => auth('admin')->id(),
                    ]);
                }
            });
            session()->flash('success', 'تم إضافة الخزنة بنجاح');
        }

        return redirect()->route('treasuries.index');
    }

    public function render()
    {
        return view('livewire.treasuries.treasury-form');
    }
}
