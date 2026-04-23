<?php

namespace App\Livewire\MeasurementUnits;

use App\Models\Unit;
use App\Services\UnitService;
use Livewire\Component;

class UnitForm extends Component
{
    public ?int $unitId = null;
    public string $name = '';
    public string $symbol = '';
    public string $type = 'weight';
    public ?int $base_unit_id = null;
    public string $conversion_factor = '1';
    public bool $is_active = true;

    public function mount(UnitService $unitService, ?int $id = null)
    {
        if ($id) {
            $this->unitId = $id;
            $unit = $unitService->getUnitById($id);
            $this->name = $unit->name;
            $this->symbol = $unit->symbol;
            $this->type = $unit->type;
            $this->base_unit_id = $unit->base_unit_id;
            $this->conversion_factor = (string) ($unit->conversion_factor * 1);
            $this->is_active = $unit->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20',
            'type' => 'required|in:weight,volume,quantity,length',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم الوحدة مطلوب',
            'symbol.required' => 'رمز الوحدة مطلوب',
            'type.required' => 'نوع الوحدة مطلوب',
            'conversion_factor.required' => 'معامل التحويل مطلوب',
            'conversion_factor.min' => 'معامل التحويل يجب أن يكون أكبر من صفر',
        ];
    }

    public function updatedType()
    {
        $this->base_unit_id = null;
    }

    public function save(UnitService $unitService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'symbol' => $this->symbol,
            'type' => $this->type,
            'base_unit_id' => $this->base_unit_id ?: null,
            'conversion_factor' => $this->base_unit_id ? $this->conversion_factor : 1,
            'is_active' => $this->is_active,
        ];

        if ($this->unitId) {
            $unitService->updateUnit($this->unitId, $data);
            session()->flash('success', 'تم تحديث الوحدة بنجاح');
        } else {
            $unitService->createUnit($data);
            session()->flash('success', 'تم إضافة الوحدة بنجاح');
        }

        return redirect()->route('units.index');
    }

    public function render()
    {
        $baseUnits = [];
        if ($this->type) {
            $query = Unit::where('type', $this->type)->whereNull('base_unit_id');
            if ($this->unitId) {
                $query->where('id', '!=', $this->unitId);
            }
            $baseUnits = $query->get();
        }

        return view('livewire.measurement-units.unit-form', [
            'typeLabels' => Unit::typeLabels(),
            'baseUnits' => $baseUnits,
        ]);
    }
}
