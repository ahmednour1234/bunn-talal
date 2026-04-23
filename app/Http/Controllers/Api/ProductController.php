<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * List Products by Category
     *
     * Returns active products in a category. The category must be assigned to the delegate.
     *
     * @group Reference Data
     *
     * @urlParam category integer required The category ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب المنتجات بنجاح",
     *   "data": [{
     *     "id": 1, "name": "منتج A", "image": null,
     *     "selling_price": 100, "discount": 0, "discount_type": "fixed",
     *     "net_price": 100, "final_price": 115,
     *     "unit": {"id": 1, "name": "كيلو"},
     *     "tax": {"id": 1, "name": "ضريبة القيمة المضافة", "rate": 15, "type": "percentage"}
     *   }],
     *   "code": 200
     * }
     * @response 403 scenario="Category not assigned" {"status": false, "message": "هذه الفئة غير مخصصة لك", "data": null, "code": 403}
     */
    public function index(Request $request, Category $category): JsonResponse
    {
        $delegate = $request->user();

        // Verify the category is actually assigned to this delegate
        $assigned = $delegate->categories()->where('categories.id', $category->id)->exists();

        if (!$assigned) {
            return $this->forbiddenResponse('هذه الفئة غير مخصصة لك');
        }

        $products = $category->products()
            ->where('is_active', true)
            ->with(['unit:id,name', 'tax:id,name,rate,type'])
            ->select('id', 'name', 'image', 'category_id', 'unit_id', 'tax_id', 'selling_price', 'discount', 'discount_type')
            ->get()
            ->map(function ($product) {
                return [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'image'          => $product->image ? asset('storage/' . $product->image) : null,
                    'selling_price'  => $product->selling_price,
                    'discount'       => $product->discount,
                    'discount_type'  => $product->discount_type,
                    'net_price'      => $product->net_price,
                    'final_price'    => $product->final_price,
                    'unit'           => $product->unit ? ['id' => $product->unit->id, 'name' => $product->unit->name] : null,
                    'tax'            => $product->tax ? [
                        'id'   => $product->tax->id,
                        'name' => $product->tax->name,
                        'rate' => $product->tax->rate,
                        'type' => $product->tax->type,
                    ] : null,
                ];
            });

        return $this->successResponse($products, 'تم جلب المنتجات بنجاح');
    }
}
