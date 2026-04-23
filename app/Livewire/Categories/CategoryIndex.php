<?php

namespace App\Livewire\Categories;

use App\Services\CategoryService;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, CategoryService $categoryService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('categories.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $category = $categoryService->toggleActive($id);
        session()->flash('success', $category->is_active ? 'تم تفعيل التصنيف' : 'تم تعطيل التصنيف');
    }

    public function delete(int $id, CategoryService $categoryService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('categories.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $categoryService->deleteCategory($id);
        session()->flash('success', 'تم حذف التصنيف بنجاح');
    }

    public function render(CategoryService $categoryService)
    {
        return view('livewire.categories.category-index', [
            'categories' => $categoryService->paginateCategories(10, $this->search ?: null),
        ]);
    }
}
