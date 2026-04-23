<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, SupplierService $supplierService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('suppliers.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $supplier = $supplierService->toggleActive($id);
        session()->flash('success', $supplier->is_active ? 'تم تفعيل المورد' : 'تم تعطيل المورد');
    }

    public function delete(int $id, SupplierService $supplierService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('suppliers.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $supplierService->deleteSupplier($id);
        session()->flash('success', 'تم حذف المورد بنجاح');
    }

    public function render()
    {
        $query = Supplier::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%");
            });
        }

        return view('livewire.suppliers.supplier-index', [
            'suppliers' => $query->latest()->paginate(10),
        ]);
    }
}
