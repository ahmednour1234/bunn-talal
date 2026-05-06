<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Category;
use App\Models\InventoryDispatch;
use App\Models\Trip;
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
        $delegate    = $request->user();
        $branchIds   = $delegate->branches()->pluck('branches.id');

        // Verify the category is actually assigned to this delegate
        $assigned = $delegate->categories()->where('categories.id', $category->id)->exists();

        if (!$assigned) {
            return $this->forbiddenResponse('هذه الفئة غير مخصصة لك');
        }

        $products = $category->products()
            ->where('is_active', true)
            ->with([
                'unit.derivedUnits',
                'unit.baseUnit.derivedUnits',
                'tax:id,name,rate,type',
                'branches' => fn ($q) => $q->whereIn('branch_id', $branchIds),
            ])
            ->select('id', 'name', 'image', 'category_id', 'unit_id', 'tax_id', 'selling_price', 'discount', 'discount_type')
            ->get()
            ->each(function ($product) {
                $product->available_stock = (float) $product->branches->sum('pivot.quantity');
            });

        return $this->successResponse(ProductResource::collection($products)->resolve(), 'تم جلب المنتجات بنجاح');
    }

    /**
     * List Trip Products
     *
     * Returns all products loaded in the delegate's vehicle for their current active trip,
     * with remaining quantity, price, tax, and all available units per product.
     *
     * @group Reference Data
     *
     * @queryParam category_id integer optional Filter by category ID. Example: 2
     * @queryParam search string optional Search by product name. Example: بن
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب منتجات الرحلة بنجاح",
     *   "data": [{"id": 1, "name": "بن حرازي", "available_quantity": 50,
     *     "available_units": [{"id": 2, "name": "كيلوجرام", "price": 35000, "available_quantity": 50}]}],
     *   "code": 200
     * }
     * @response 404 scenario="No active trip" {"status": false, "message": "لا توجد رحلة نشطة حالياً", "data": null, "code": 404}
     */
    public function tripProducts(Request $request, Trip $trip = null): JsonResponse
    {
        $delegate = $request->user();

        if ($trip && $trip->exists) {
            // Trip was provided via route model binding — verify ownership
            if ($trip->delegate_id !== $delegate->id) {
                return $this->forbiddenResponse('ليس لديك صلاحية للوصول إلى هذه الرحلة');
            }
        } else {
            // Find the delegate's current active trip automatically
            $trip = Trip::where('delegate_id', $delegate->id)
                ->whereIn('status', ['active', 'in_transit'])
                ->latest()
                ->first();

            if (!$trip) {
                return $this->notFoundResponse('لا توجد رحلة نشطة حالياً');
            }
        }

        // Collect remaining quantities per product from dispatches of this trip
        $dispatches = InventoryDispatch::where('trip_id', $trip->id)
            ->where('delegate_id', $delegate->id)
            ->with('items')
            ->get();

        // remaining = dispatched - returned (in dispatch unit, which is product's base unit)
        $quantities = [];
        foreach ($dispatches as $dispatch) {
            foreach ($dispatch->items as $item) {
                $remaining = $item->quantity - $item->returned_quantity;
                $quantities[$item->product_id] = ($quantities[$item->product_id] ?? 0) + $remaining;
            }
        }

        if (empty($quantities)) {
            return $this->successResponse([], 'لا توجد منتجات في هذه الرحلة');
        }

        $productIds = array_keys($quantities);

        $query = \App\Models\Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->with([
                'unit.derivedUnits',
                'unit.baseUnit.derivedUnits',
                'tax:id,name,rate,type',
                'category:id,name',
            ]);

        // Optional filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $products = $query->get()
            ->each(function ($product) use ($quantities) {
                $product->available_stock = (float) ($quantities[$product->id] ?? 0);
            });

        return $this->successResponse(ProductResource::collection($products)->resolve(), 'تم جلب منتجات الرحلة بنجاح');
    }
}
