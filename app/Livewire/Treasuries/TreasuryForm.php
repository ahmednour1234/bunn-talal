<?php

namespace App\Livewire\Treasuries;

use App\Services\TreasuryService;
use Livewire\Component;

class TreasuryForm extends Component
{
    public ?int $treasuryId = null;
    public string $name = '';
    public string $balance = '0';
    public bool $is_active = true;

    public function mount(TreasuryService $treasuryService, ?int $id = null)
    {
        if ($id) {
            $this->treasuryId = $id;
            $treasury = $treasuryService->getTreasuryById($id);
            $this->name = $treasury->name;
            $this->balance = (string) ($treasury->balance * 1);
            $this->is_active = $treasury->is_active;
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
            $treasuryService->updateTreasury($this->treasuryId, $data);
            session()->flash('success', 'تم تحديث الخزنة بنجاح');
        } else {
            $treasuryService->createTreasury($data);
            session()->flash('success', 'تم إضافة الخزنة بنجاح');
        }

        return redirect()->route('treasuries.index');
    }

    public function render()
    {
        return view('livewire.treasuries.treasury-form');
    }
}
