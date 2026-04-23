<?php

namespace App\Livewire\Branches;

use App\Services\BranchService;
use Livewire\Component;

class BranchForm extends Component
{
    public ?int $branchId = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public ?string $latitude = null;
    public ?string $longitude = null;

    public function mount(BranchService $branchService, ?int $id = null)
    {
        if ($id) {
            $this->branchId = $id;
            $branch = $branchService->getBranchById($id);
            $this->name = $branch->name;
            $this->phone = $branch->phone ?? '';
            $this->email = $branch->email ?? '';
            $this->latitude = $branch->latitude;
            $this->longitude = $branch->longitude;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم الفرع مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180',
        ];
    }

    public function save(BranchService $branchService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
        ];

        if ($this->branchId) {
            $branchService->updateBranch($this->branchId, $data);
            session()->flash('success', 'تم تحديث الفرع بنجاح');
        } else {
            $branchService->createBranch($data);
            session()->flash('success', 'تم إضافة الفرع بنجاح');
        }

        return redirect()->route('branches.index');
    }

    public function render()
    {
        return view('livewire.branches.branch-form');
    }
}
