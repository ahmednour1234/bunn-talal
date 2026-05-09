<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * List Categories
     *
     * Returns active product categories assigned to the authenticated delegate.
     *
     * @group Reference Data
     *
     * @response 200 {"status": true, "message": "تم جلب الفئات بنجاح", "data": [{"id": 1, "name": "أجهزة", "image": null}], "code": 200}
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $request->user()
            ->categories()
            ->where('categories.is_active', true)
            ->select('categories.id', 'categories.name', 'categories.image')
            ->get();

        return $this->successResponse(CategoryResource::collection($categories)->resolve(), 'تم جلب الفئات بنجاح');
    }

    /**
     * List All Categories
     *
     * Returns all active categories regardless of delegate assignment. For display purposes.
     *
     * @group Reference Data
     *
     * @response 200 {"status": true, "message": "تم جلب الفئات بنجاح", "data": [{"id": 1, "name": "أجهزة", "image": null}], "code": 200}
     */
    public function allCategories(): JsonResponse
    {
        $categories = \App\Models\Category::where('is_active', true)
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->get();

        return $this->successResponse(CategoryResource::collection($categories)->resolve(), 'تم جلب الفئات بنجاح');
    }
}
