<?php

namespace App\Livewire\Vehicles;

use App\Services\VehicleService;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, VehicleService $vehicleService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('vehicles.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $vehicle = $vehicleService->toggleActive($id);
        session()->flash('success', $vehicle->is_active ? 'تم تفعيل المركبة' : 'تم تعطيل المركبة');
    }

    public function delete(int $id, VehicleService $vehicleService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('vehicles.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $vehicleService->deleteVehicle($id);
        session()->flash('success', 'تم حذف المركبة بنجاح');
    }

    public function render(VehicleService $vehicleService)
    {
        return view('livewire.vehicles.vehicle-index', [
            'vehicles' => $vehicleService->paginateVehicles(10, $this->search ?: null),
        ]);
    }
}
