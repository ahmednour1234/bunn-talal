<?php

namespace App\Livewire\Areas;

use App\Services\AreaService;
use Livewire\Component;
use Livewire\WithPagination;

class AreaIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, AreaService $areaService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('areas.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $area = $areaService->toggleActive($id);
        session()->flash('success', $area->is_active ? 'تم تفعيل المنطقة' : 'تم تعطيل المنطقة');
    }

    public function delete(int $id, AreaService $areaService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('areas.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $areaService->deleteArea($id);
        session()->flash('success', 'تم حذف المنطقة بنجاح');
    }

    public function render(AreaService $areaService)
    {
        return view('livewire.areas.area-index', [
            'areas' => $areaService->paginateAreas(10, $this->search ?: null),
        ]);
    }
}
