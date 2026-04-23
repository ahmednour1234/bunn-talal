<?php

namespace App\Livewire\ProductDepreciations;

use App\Services\ProductDepreciationService;
use Livewire\Component;

class ProductDepreciationShow extends Component
{
    public int $depreciationId;

    public function mount(int $id)
    {
        $this->depreciationId = $id;
    }

    public function approve(ProductDepreciationService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('product-depreciations.approve')) {
            session()->flash('error', 'ليس لديك صلاحية الموافقة');
            return;
        }

        try {
            $service->approveDepreciation($this->depreciationId, $admin->id);
            session()->flash('success', 'تمت الموافقة وتم خصم الكميات من المخزون');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function reject(ProductDepreciationService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('product-depreciations.approve')) {
            session()->flash('error', 'ليس لديك صلاحية الرفض');
            return;
        }

        try {
            $service->rejectDepreciation($this->depreciationId, $admin->id);
            session()->flash('success', 'تم رفض طلب الإهلاك');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(ProductDepreciationService $service)
    {
        $depreciation = $service->getById($this->depreciationId);
        $depreciation->load(['items.product', 'items.unit', 'branch', 'admin', 'approvedByAdmin']);

        return view('livewire.product-depreciations.product-depreciation-show', [
            'depreciation' => $depreciation,
        ]);
    }
}
