<?php

namespace App\Livewire\Suppliers;

use App\Services\SupplierService;
use Livewire\Component;

class SupplierForm extends Component
{
    public ?int $supplierId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $company_name = '';
    public string $tax_number = '';
    public string $address = '';
    public string $opening_balance = '0';
    public string $credit_limit = '0';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(SupplierService $supplierService, ?int $id = null)
    {
        if ($id) {
            $this->supplierId = $id;
            $supplier = $supplierService->getSupplierById($id);
            $this->name = $supplier->name;
            $this->email = $supplier->email ?? '';
            $this->phone = $supplier->phone ?? '';
            $this->company_name = $supplier->company_name ?? '';
            $this->tax_number = $supplier->tax_number ?? '';
            $this->address = $supplier->address ?? '';
            $this->opening_balance = (string) ($supplier->opening_balance * 1);
            $this->credit_limit = (string) ($supplier->credit_limit * 1);
            $this->notes = $supplier->notes ?? '';
            $this->is_active = $supplier->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'required|numeric|min:0',
            'credit_limit' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المورد مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'opening_balance.min' => 'الرصيد الافتتاحي يجب أن يكون 0 أو أكثر',
            'credit_limit.min' => 'الحد الائتماني يجب أن يكون 0 أو أكثر',
        ];
    }

    public function save(SupplierService $supplierService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'company_name' => $this->company_name ?: null,
            'tax_number' => $this->tax_number ?: null,
            'address' => $this->address ?: null,
            'opening_balance' => $this->opening_balance,
            'credit_limit' => $this->credit_limit,
            'notes' => $this->notes ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->supplierId) {
            $supplierService->updateSupplier($this->supplierId, $data);
            session()->flash('success', 'تم تحديث بيانات المورد بنجاح');
        } else {
            $supplierService->createSupplier($data);
            session()->flash('success', 'تم إضافة المورد بنجاح');
        }

        return redirect()->route('suppliers.index');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-form');
    }
}
