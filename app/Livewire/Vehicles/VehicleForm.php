<?php

namespace App\Livewire\Vehicles;

use App\Services\VehicleService;
use Livewire\Component;

class VehicleForm extends Component
{
    public ?int $vehicleId = null;
    public string $name = '';
    public string $code = '';
    public bool $is_active = true;

    public function mount(VehicleService $vehicleService, ?int $id = null)
    {
        if ($id) {
            $this->vehicleId = $id;
            $vehicle = $vehicleService->getVehicleById($id);
            $this->name = $vehicle->name;
            $this->code = $vehicle->code;
            $this->is_active = $vehicle->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:vehicles,code,' . ($this->vehicleId ?? 'NULL'),
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المركبة مطلوب',
            'code.required' => 'كود المركبة مطلوب',
            'code.unique' => 'كود المركبة مستخدم مسبقاً',
        ];
    }

    public function save(VehicleService $vehicleService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'is_active' => $this->is_active,
        ];

        if ($this->vehicleId) {
            $vehicleService->updateVehicle($this->vehicleId, $data);
            session()->flash('success', 'تم تحديث المركبة بنجاح');
        } else {
            $vehicleService->createVehicle($data);
            session()->flash('success', 'تم إضافة المركبة بنجاح');
        }

        return redirect()->route('vehicles.index');
    }

    public function render()
    {
        return view('livewire.vehicles.vehicle-form');
    }
}
