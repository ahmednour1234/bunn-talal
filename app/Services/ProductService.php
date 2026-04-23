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

    public function updateBranchQuantity(int $productId, int $branchId, int $quantity, ?int $unitId = null)
    {
        $product = $this->productRepository->getById($productId);
        $product->branches()->syncWithoutDetaching([
            $branchId => ['quantity' => $quantity, 'unit_id' => $unitId],
        ]);
    }

    public function getProductsByBranch(int $branchId)
    {
        return $this->productRepository->getProductsByBranch($branchId);
    }
}
