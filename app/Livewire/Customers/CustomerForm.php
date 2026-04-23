<?php

namespace App\Livewire\Customers;

use App\Models\Area;
use App\Models\Customer;
use App\Services\CustomerService;
use Livewire\Component;

class CustomerForm extends Component
{
    public ?int $customerId = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public ?int $area_id = null;
    public string $address = '';
    public ?string $latitude = null;
    public ?string $longitude = null;
    public string $credit_limit = '0';
    public string $opening_balance = '0';
    public string $classification = 'regular';
    public bool $is_active = true;

    public function mount(CustomerService $customerService, ?int $id = null)
    {
        if ($id) {
            $this->customerId = $id;
            $customer = $customerService->getCustomerById($id);
            $this->name = $customer->name;
            $this->phone = $customer->phone ?? '';
            $this->email = $customer->email ?? '';
            $this->area_id = $customer->area_id;
            $this->address = $customer->address ?? '';
            $this->latitude = $customer->latitude;
            $this->longitude = $customer->longitude;
            $this->credit_limit = (string) ($customer->credit_limit * 1);
            $this->opening_balance = (string) ($customer->opening_balance * 1);
            $this->classification = $customer->classification;
            $this->is_active = $customer->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'area_id' => 'nullable|exists:areas,id',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'credit_limit' => 'required|numeric|min:0',
            'opening_balance' => 'required|numeric|min:0',
            'classification' => 'required|in:premium,regular,medium',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم العميل مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'credit_limit.min' => 'الحد الائتماني يجب أن يكون 0 أو أكثر',
            'opening_balance.min' => 'الرصيد الافتتاحي يجب أن يكون 0 أو أكثر',
        ];
    }

    public function save(CustomerService $customerService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'area_id' => $this->area_id ?: null,
            'address' => $this->address ?: null,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'credit_limit' => $this->credit_limit,
            'opening_balance' => $this->opening_balance,
            'classification' => $this->classification,
            'is_active' => $this->is_active,
        ];

        if ($this->customerId) {
            $customerService->updateCustomer($this->customerId, $data);
            session()->flash('success', 'تم تحديث بيانات العميل بنجاح');
        } else {
            $customerService->createCustomer($data);
            session()->flash('success', 'تم إضافة العميل بنجاح');
        }

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.customer-form', [
            'classificationLabels' => Customer::classificationLabels(),
            'areas' => Area::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
