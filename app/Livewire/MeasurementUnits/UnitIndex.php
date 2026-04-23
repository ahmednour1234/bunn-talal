<?php

namespace App\Livewire\MeasurementUnits;

use App\Models\Unit;
use App\Services\UnitService;
use Livewire\Component;
use Livewire\WithPagination;

class UnitIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, UnitService $unitService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('units.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $unit = $unitService->toggleActive($id);
        session()->flash('success', $unit->is_active ? 'تم تفعيل الوحدة' : 'تم تعطيل الوحدة');
    }

    public function delete(int $id, UnitService $unitService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('units.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $unit = $unitService->getUnitById($id);
        if ($unit->derivedUnits()->count() > 0) {
            session()->flash('error', 'لا يمكن حذف وحدة أساسية مرتبطة بوحدات أخرى');
            return;
        }

        $unitService->deleteUnit($id);
        session()->flash('success', 'تم حذف وحدة القياس بنجاح');
    }

    public function render(UnitService $unitService)
    {
        $query = Unit::query()->with('baseUnit');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        return view('livewire.measurement-units.unit-index', [
            'units' => $query->latest()->paginate(10),
            'typeLabels' => Unit::typeLabels(),
        ]);
    }
}
