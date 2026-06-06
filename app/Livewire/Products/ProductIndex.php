<?php

namespace App\Livewire\Products;

use App\Models\Branch;
use App\Models\Product;
use App\Services\ProductService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $branchFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
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
        $query = Product::with(['category', 'unit', 'tax', 'branches']);

        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$s}%"));
            });
        }

        if ($this->branchFilter) {
            $query->whereHas('branches', fn($q) => $q->where('branch_id', $this->branchFilter));
        }

        return view('livewire.products.product-index', [
            'products' => $query->latest()->paginate(10),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
