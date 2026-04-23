<?php

namespace App\Livewire\Categories;

use App\Services\CategoryService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CategoryForm extends Component
{
    use WithFileUploads;

    public ?int $categoryId = null;
    public string $name = '';
    public bool $is_active = true;
    public $image;
    public ?string $existingImage = null;

    public function mount(CategoryService $categoryService, ?int $id = null)
    {
        if ($id) {
            $this->categoryId = $id;
            $category = $categoryService->getCategoryById($id);
            $this->name = $category->name;
            $this->is_active = $category->is_active;
            $this->existingImage = $category->image;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'image' => $this->categoryId ? 'nullable|image|max:2048' : 'nullable|image|max:2048',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم التصنيف مطلوب',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 2 ميجا',
        ];
    }

    public function save(CategoryService $categoryService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'is_active' => $this->is_active,
        ];

        $uploadedImage = $this->image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $this->image : null;

        if ($this->categoryId) {
            $categoryService->updateCategory($this->categoryId, $data, $uploadedImage);
            session()->flash('success', 'تم تحديث التصنيف بنجاح');
        } else {
            $categoryService->createCategory($data, $uploadedImage);
            session()->flash('success', 'تم إضافة التصنيف بنجاح');
        }

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.categories.category-form');
    }
}
