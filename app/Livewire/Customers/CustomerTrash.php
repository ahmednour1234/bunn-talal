<?php

namespace App\Livewire\Customers;

use App\Services\CustomerService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerTrash extends Component
{
    use WithPagination;

    public function restore(int $id, CustomerService $customerService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('customers.delete')) {
            session()->flash('error', 'ليس لديك صلاحية لاستعادة العملاء');
            return;
        }

        $customerService->restoreCustomer($id);
        session()->flash('success', 'تم استعادة العميل بنجاح');
    }

    public function forceDelete(int $id, CustomerService $customerService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('customers.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف النهائي');
            return;
        }

        $customerService->forceDeleteCustomer($id);
        session()->flash('success', 'تم حذف العميل نهائياً');
    }

    public function render(CustomerService $customerService)
    {
        return view('livewire.customers.customer-trash', [
            'customers' => $customerService->getTrashedCustomers(),
        ]);
    }
}
