<?php

namespace App\Livewire\Delegates;

use App\Models\Area;
use App\Models\Branch;
use App\Models\Category;
use App\Services\DelegateService;
use Livewire\Component;
use Livewire\WithFileUploads;

class DelegateForm extends Component
{
    use WithFileUploads;

    public ?int $delegateId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $national_id = '';
    public $national_id_image;
    public ?string $existingIdImage = null;
    public string $password = '';
    public string $credit_sales_limit = '0';
    public string $cash_custody = '0';
    public string $total_collected = '0';
    public string $total_due = '0';
    public string $sales_commission_rate = '0';
    public ?string $current_latitude = null;
    public ?string $current_longitude = null;
    public bool $is_active = true;

    public array $selectedBranches = [];
    public array $selectedAreas = [];
    public array $selectedCategories = [];

    public function mount(DelegateService $delegateService, ?int $id = null)
    {
        if ($id) {
            $this->delegateId = $id;
            $delegate = $delegateService->getDelegateById($id);
            $this->name = $delegate->name;
            $this->email = $delegate->email ?? '';
            $this->phone = $delegate->phone ?? '';
            $this->national_id = $delegate->national_id ?? '';
            $this->existingIdImage = $delegate->national_id_image;
            $this->credit_sales_limit = (string) ($delegate->credit_sales_limit * 1);
            $this->cash_custody = (string) ($delegate->cash_custody * 1);
            $this->total_collected = (string) ($delegate->total_collected * 1);
            $this->total_due = (string) ($delegate->total_due * 1);
            $this->sales_commission_rate = (string) ($delegate->sales_commission_rate * 1);
            $this->current_latitude = $delegate->current_latitude;
            $this->current_longitude = $delegate->current_longitude;
            $this->is_active = $delegate->is_active;
            $this->selectedBranches = $delegate->branches->pluck('id')->map(fn ($v) => (string) $v)->toArray();
            $this->selectedAreas = $delegate->areas->pluck('id')->map(fn ($v) => (string) $v)->toArray();
            $this->selectedCategories = $delegate->categories->pluck('id')->map(fn ($v) => (string) $v)->toArray();
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:30',
            'national_id_image' => 'nullable|image|max:2048',
            'credit_sales_limit' => 'required|numeric|min:0',
            'cash_custody' => 'required|numeric|min:0',
            'total_collected' => 'required|numeric|min:0',
            'total_due' => 'required|numeric|min:0',
            'sales_commission_rate' => 'required|numeric|min:0|max:100',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'selectedBranches' => 'array',
            'selectedAreas' => 'array',
            'selectedCategories' => 'array',
        ];

        if (!$this->delegateId) {
            $rules['password'] = 'required|string|min:6';
        } else {
            $rules['password'] = 'nullable|string|min:6';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المندوب مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'national_id_image.image' => 'يجب أن يكون الملف صورة',
            'national_id_image.max' => 'حجم الصورة يجب أن لا يتجاوز 2 ميجا',
            'sales_commission_rate.max' => 'نسبة العمولة يجب أن لا تتجاوز 100%',
        ];
    }

    public function save(DelegateService $delegateService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'national_id' => $this->national_id ?: null,
            'credit_sales_limit' => $this->credit_sales_limit,
            'cash_custody' => $this->cash_custody,
            'total_collected' => $this->total_collected,
            'total_due' => $this->total_due,
            'sales_commission_rate' => $this->sales_commission_rate,
            'current_latitude' => $this->current_latitude ?: null,
            'current_longitude' => $this->current_longitude ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        $uploadedImage = $this->national_id_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $this->national_id_image : null;

        $branchIds = array_map('intval', $this->selectedBranches);
        $areaIds = array_map('intval', $this->selectedAreas);
        $categoryIds = array_map('intval', $this->selectedCategories);

        if ($this->delegateId) {
            $delegateService->updateDelegate($this->delegateId, $data, $uploadedImage, $branchIds, $areaIds, $categoryIds);
            session()->flash('success', 'تم تحديث بيانات المندوب بنجاح');
        } else {
            $delegateService->createDelegate($data, $uploadedImage, $branchIds, $areaIds, $categoryIds);
            session()->flash('success', 'تم إضافة المندوب بنجاح');
        }

        return redirect()->route('delegates.index');
    }

    public function render()
    {
        return view('livewire.delegates.delegate-form', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'areas' => Area::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
