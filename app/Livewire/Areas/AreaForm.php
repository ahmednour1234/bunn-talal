<?php

namespace App\Livewire\Areas;

use App\Services\AreaService;
use Livewire\Component;

class AreaForm extends Component
{
    public ?int $areaId = null;
    public string $name = '';
    public bool $is_active = true;

    public function mount(AreaService $areaService, ?int $id = null)
    {
        if ($id) {
            $this->areaId = $id;
            $area = $areaService->getAreaById($id);
            $this->name = $area->name;
            $this->is_active = $area->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المنطقة مطلوب',
        ];
    }

    public function save(AreaService $areaService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'is_active' => $this->is_active,
        ];

        if ($this->areaId) {
            $areaService->updateArea($this->areaId, $data);
            session()->flash('success', 'تم تحديث المنطقة بنجاح');
        } else {
            $areaService->createArea($data);
            session()->flash('success', 'تم إضافة المنطقة بنجاح');
        }

        return redirect()->route('areas.index');
    }

    public function render()
    {
        return view('livewire.areas.area-form');
    }
}
