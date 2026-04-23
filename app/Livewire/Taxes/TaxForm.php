<?php

namespace App\Livewire\Taxes;

use App\Services\TaxService;
use Livewire\Component;

class TaxForm extends Component
{
    public ?int $taxId = null;
    public string $name = '';
    public string $rate = '0';
    public string $type = 'percentage';
    public bool $is_active = true;

    public function mount(TaxService $taxService, ?int $id = null)
    {
        if ($id) {
            $this->taxId = $id;
            $tax = $taxService->getTaxById($id);
            $this->name = $tax->name;
            $this->rate = (string) $tax->rate;
            $this->type = $tax->type;
            $this->is_active = $tax->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم الضريبة مطلوب',
            'rate.required' => 'النسبة / المبلغ مطلوب',
            'rate.numeric' => 'يجب أن تكون القيمة رقماً',
            'type.required' => 'نوع الضريبة مطلوب',
        ];
    }

    public function save(TaxService $taxService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'rate' => $this->rate,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];

        if ($this->taxId) {
            $taxService->updateTax($this->taxId, $data);
            session()->flash('success', 'تم تحديث الضريبة بنجاح');
        } else {
            $taxService->createTax($data);
            session()->flash('success', 'تم إضافة الضريبة بنجاح');
        }

        return redirect()->route('taxes.index');
    }

    public function render()
    {
        return view('livewire.taxes.tax-form');
    }
}
