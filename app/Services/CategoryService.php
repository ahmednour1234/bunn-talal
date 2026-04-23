<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categoryRepository)
    {
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->getAll();
    }

    public function getCategoryById(int $id)
    {
        return $this->categoryRepository->getById($id);
    }

    public function createCategory(array $data, ?UploadedFile $image = null)
    {
        if ($image) {
            $data['image'] = $image->store('categories', 'public');
        }

        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data, ?UploadedFile $image = null)
    {
        if ($image) {
            $category = $this->categoryRepository->getById($id);
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $image->store('categories', 'public');
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->getById($id);
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        return $this->categoryRepository->delete($id);
    }

    public function paginateCategories(int $perPage = 15, ?string $search = null)
    {
        return $this->categoryRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $category = $this->categoryRepository->getById($id);
        $category->update(['is_active' => !$category->is_active]);
        return $category;
    }
}
