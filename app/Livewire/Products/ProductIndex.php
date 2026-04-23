<?php

namespace App\Livewire\Products;

use App\Services\ProductService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, ProductService $productService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('products.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $product = $productService->toggleActive($id);
        session()->flash('success', $product->is_active ? 'تم تفعيل المنتج' : 'تم تعطيل المنتج');
    }

    public function delete(int $id, ProductService $productService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('products.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $productService->deleteProduct($id);
        session()->flash('success', 'تم حذف المنتج بنجاح');
    }

    public function render(ProductService $productService)
    {
        return view('livewire.products.product-index', [
            'products' => $productService->paginateProducts(10, $this->search ?: null),
        ]);
    }
}
