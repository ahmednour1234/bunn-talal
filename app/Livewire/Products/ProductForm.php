<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Branch;
use App\Services\ProductService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?int $productId = null;
    public string $name = '';
    public ?int $category_id = null;
    public ?int $unit_id = null;
    public string $cost_price = '0';
    public string $selling_price = '0';
    public string $discount = '0';
    public string $discount_type = 'fixed';
    public ?int $tax_id = null;
    public bool $is_active = true;
    public $image;
    public ?string $existingImage = null;

    // Branch quantities and units
    public array $branch_quantities = [];
    public array $branch_units = [];

    public function mount(ProductService $productService, ?int $id = null)
    {
        if ($id) {
            $this->productId = $id;
            $product = $productService->getProductById($id);
            $this->name = $product->name;
            $this->category_id = $product->category_id;
            $this->unit_id = $product->unit_id;
            $this->cost_price = (string) $product->cost_price;
            $this->selling_price = (string) $product->selling_price;
            $this->discount = (string) $product->discount;
            $this->discount_type = $product->discount_type ?? 'fixed';
            $this->tax_id = $product->tax_id;
            $this->is_active = $product->is_active;
            $this->existingImage = $product->image;

            foreach ($product->branches as $branch) {
                $this->branch_quantities[$branch->id] = (string) $branch->pivot->quantity;
                $this->branch_units[$branch->id] = (string) ($branch->pivot->unit_id ?? $product->unit_id ?? '');
            }
        }

        // Initialize all branches
        $branches = Branch::where('is_active', true)->get();
        foreach ($branches as $branch) {
            if (!isset($this->branch_quantities[$branch->id])) {
                $this->branch_quantities[$branch->id] = '0';
                $this->branch_units[$branch->id] = '';
            }
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'tax_id' => 'nullable|exists:taxes,id',
            'is_active' => 'boolean',
            'image' => $this->productId ? 'nullable|image|max:2048' : 'nullable|image|max:2048',
            'branch_quantities.*' => 'nullable|integer|min:0',
            'branch_units.*' => 'nullable|exists:units,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'category_id.required' => 'التصنيف مطلوب',
            'unit_id.required' => 'وحدة القياس مطلوبة',
            'cost_price.required' => 'سعر التكلفة مطلوب',
            'selling_price.required' => 'سعر البيع مطلوب',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 2 ميجا',
        ];
    }

    public function save(ProductService $productService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'discount' => $this->discount ?: 0,
            'discount_type' => $this->discount_type,
            'tax_id' => $this->tax_id,
            'is_active' => $this->is_active,
        ];

        $uploadedImage = $this->image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $this->image : null;

        if ($this->productId) {
            $product = $productService->updateProduct($this->productId, $data, $uploadedImage);
            session()->flash('success', 'تم تحديث المنتج بنجاح');
        } else {
            $product = $productService->createProduct($data, $uploadedImage);
            session()->flash('success', 'تم إضافة المنتج بنجاح');
        }

        // Update branch quantities
        foreach ($this->branch_quantities as $branchId => $qty) {
            $unitId = !empty($this->branch_units[$branchId]) ? (int) $this->branch_units[$branchId] : $product->unit_id;
            $productService->updateBranchQuantity($product->id, (int) $branchId, (int) $qty, $unitId);
        }

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.product-form', [
            'categories' => Category::where('is_active', true)->get(),
            'units' => Unit::where('is_active', true)->get(),
            'taxes' => Tax::where('is_active', true)->get(),
            'branches' => Branch::where('is_active', true)->get(),
        ]);
    }
}
