<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\CustomerService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $classificationFilter = '';
    public string $areaFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClassificationFilter()
    {
        $this->resetPage();
    }

    public function updatingAreaFilter()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, CustomerService $customerService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('customers.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $customer = $customerService->toggleActive($id);
        session()->flash('success', $customer->is_active ? 'تم تفعيل العميل' : 'تم تعطيل العميل');
    }

    public function delete(int $id, CustomerService $customerService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('customers.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $customerService->deleteCustomer($id);
        session()->flash('success', 'تم حذف العميل بنجاح');
    }

    public function render()
    {
        $query = Customer::query()->with('area');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->classificationFilter) {
            $query->where('classification', $this->classificationFilter);
        }

        if ($this->areaFilter) {
            $query->where('area_id', $this->areaFilter);
        }

        return view('livewire.customers.customer-index', [
            'customers' => $query->latest()->paginate(10),
            'classificationLabels' => Customer::classificationLabels(),
            'areas' => \App\Models\Area::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
