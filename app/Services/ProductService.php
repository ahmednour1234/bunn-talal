<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductService
{
    public function __construct(protected ProductRepositoryInterface $productRepository)
    {
    }

    public function getAllProducts()
    {
        return $this->productRepository->getAll();
    }

    public function getActiveProducts()
    {
        return $this->productRepository->getActiveProducts();
    }

    public function getProductById(int $id)
    {
        return $this->productRepository->getById($id);
    }

    public function createProduct(array $data, ?TemporaryUploadedFile $image = null)
    {
        if ($image) {
            $data['image'] = $image->store('products', 'public');
        }
        return $this->productRepository->create($data);
    }

    public function updateProduct(int $id, array $data, ?TemporaryUploadedFile $image = null)
    {
        $product = $this->productRepository->getById($id);

        if ($image) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $image->store('products', 'public');
        }

        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->getById($id);
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        return $this->productRepository->delete($id);
    }

    public function paginateProducts(int $perPage = 15, ?string $search = null)
    {
        return $this->productRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $product = $this->productRepository->getById($id);
        $product->update(['is_active' => !$product->is_active]);
        return $product;
    }

    public function updateBranchQuantity(int $productId, int $branchId, int $quantity, ?int $unitId = null, ?float $costPrice = null)
    {
        $product = $this->productRepository->getById($productId);

        // Log the cost change before applying it, so we can analyse profit over time.
        if ($costPrice !== null) {
            $existing = $product->branches()->where('branch_id', $branchId)->first();
            $oldCost = (float) ($existing?->pivot->cost_price ?? 0);

            if (round($oldCost, 2) !== round($costPrice, 2)) {
                \App\Models\ProductCostHistory::create([
                    'product_id' => $productId,
                    'branch_id'  => $branchId,
                    'old_cost'   => $oldCost,
                    'new_cost'   => $costPrice,
                    'admin_id'   => auth('admin')->id(),
                ]);
            }
        }

        $pivot = ['quantity' => $quantity, 'unit_id' => $unitId];
        if ($costPrice !== null) {
            $pivot['cost_price'] = $costPrice;
        }

        $product->branches()->syncWithoutDetaching([$branchId => $pivot]);
    }

    public function getProductsByBranch(int $branchId)
    {
        return $this->productRepository->getProductsByBranch($branchId);
    }
}
