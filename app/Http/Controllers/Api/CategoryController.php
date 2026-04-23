<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->where('is_active', true)
            ->select('id', 'name', 'image')
            ->get()
            ->map(function ($category) {
                return [
                    'id'    => $category->id,
                    'name'  => $category->name,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                ];
            });

        return $this->successResponse($categories, 'تم جلب الفئات بنجاح');
    }
}
