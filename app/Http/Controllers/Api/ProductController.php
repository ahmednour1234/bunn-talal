<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->map(function ($product) {
                $unit              = $product->unit;
                $availableUnits    = [];
                // Total stock in product's base unit across delegate's branches
                $stockInBaseUnit   = $product->branches->sum('pivot.quantity');

                if ($unit) {
                    // Build the full unit family (base unit + all derived of same type)
                    if ($unit->isBaseUnit()) {
                        $familyUnits = collect([$unit])->merge($unit->derivedUnits);
                    } else {
                        $base        = $unit->baseUnit;
                        $familyUnits = $base
                            ? collect([$base])->merge($base->derivedUnits)
                            : collect([$unit]);
                    }

                    $productFactor = (float) $unit->conversion_factor;

                    $availableUnits = $familyUnits->map(function ($u) use ($product, $productFactor, $stockInBaseUnit) {
                        $factor    = (float) $u->conversion_factor / $productFactor;
                        $sellPrice = round((float) $product->selling_price * $factor, 2);

                        // Stock converted to this unit
                        // productFactor = conversion of the product unit (e.g. kg=1000 if base is gram)
                        // u->conversion_factor = this unit's factor
                        // stockInBaseUnit is already in the product's unit's base
                        $stockInThisUnit = $u->conversion_factor > 0
                            ? floor($stockInBaseUnit * $productFactor / $u->conversion_factor)
                            : 0;

                        // Discount
                        $discountAmount = 0;
                        if ($product->discount > 0) {
                            if ($product->discount_type === 'percentage') {
                                $discountAmount = round($sellPrice * $product->discount / 100, 2);
                            } else {
                                $discountAmount = round((float) $product->discount * $factor, 2);
                            }
                        }
                        $netPrice = round(max(0, $sellPrice - $discountAmount), 2);

                        // Tax
                        $taxAmount = 0;
                        if ($product->tax) {
                            $taxAmount = $product->tax->type === 'percentage'
                                ? round($netPrice * $product->tax->rate / 100, 2)
                                : round((float) $product->tax->rate * $factor, 2);
                        }

                        return [
                            'id'                   => $u->id,
                            'name'                 => $u->name,
                            'symbol'               => $u->symbol,
                            'price'                => $sellPrice,
                            'discount_amount'      => $discountAmount,
                            'price_after_discount' => $netPrice,
                            'tax_name'             => $product->tax?->name,
                            'tax_rate'             => $product->tax?->rate,
                            'tax_type'             => $product->tax?->type,
                            'tax_amount'           => $taxAmount,
                            'price_with_tax'       => round($netPrice + $taxAmount, 2),
                            'available_quantity'   => (int) $stockInThisUnit,
                        ];
                    })->values()->toArray();
                }

                return [
                    'id'              => $product->id,
                    'name'            => $product->name,
                    'image'           => $product->image ? asset('storage/' . $product->image) : null,
                    'selling_price'   => $product->selling_price,
                    'discount'        => $product->discount,
                    'discount_type'   => $product->discount_type,
                    'net_price'       => $product->net_price,
                    'final_price'     => $product->final_price,
                    'unit'            => $unit ? ['id' => $unit->id, 'name' => $unit->name, 'symbol' => $unit->symbol] : null,
                    'tax'             => $product->tax ? [
                        'id'   => $product->tax->id,
                        'name' => $product->tax->name,
                        'rate' => $product->tax->rate,
                        'type' => $product->tax->type,
                    ] : null,
                    'available_units' => $availableUnits,
                ];
            });

        return $this->successResponse($products, 'تم جلب المنتجات بنجاح');
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
    public function tripProducts(Request $request): JsonResponse
    {
        $delegate = $request->user();

        // Find the delegate's current active trip automatically
        $trip = Trip::where('delegate_id', $delegate->id)
            ->whereIn('status', ['active', 'in_transit'])
            ->latest()
            ->first();

        if (!$trip) {
            return $this->notFoundResponse('لا توجد رحلة نشطة حالياً');
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

        $products = $query->get()->map(function ($product) use ($quantities) {
            $unit            = $product->unit;
            $stockInBaseUnit = $quantities[$product->id] ?? 0;
            $availableUnits  = [];

            if ($unit) {
                if ($unit->isBaseUnit()) {
                    $familyUnits = collect([$unit])->merge($unit->derivedUnits);
                } else {
                    $base        = $unit->baseUnit;
                    $familyUnits = $base
                        ? collect([$base])->merge($base->derivedUnits)
                        : collect([$unit]);
                }

                $productFactor  = (float) $unit->conversion_factor;

                $availableUnits = $familyUnits->map(function ($u) use ($product, $productFactor, $stockInBaseUnit) {
                    $factor    = (float) $u->conversion_factor / $productFactor;
                    $sellPrice = round((float) $product->selling_price * $factor, 2);

                    $discountAmount = 0;
                    if ($product->discount > 0) {
                        if ($product->discount_type === 'percentage') {
                            $discountAmount = round($sellPrice * $product->discount / 100, 2);
                        } else {
                            $discountAmount = round((float) $product->discount * $factor, 2);
                        }
                    }
                    $netPrice  = round(max(0, $sellPrice - $discountAmount), 2);

                    $taxAmount = 0;
                    if ($product->tax) {
                        $taxAmount = $product->tax->type === 'percentage'
                            ? round($netPrice * $product->tax->rate / 100, 2)
                            : round((float) $product->tax->rate * $factor, 2);
                    }

                    $stockInThisUnit = $u->conversion_factor > 0
                        ? floor($stockInBaseUnit * $productFactor / $u->conversion_factor)
                        : 0;

                    return [
                        'id'                   => $u->id,
                        'name'                 => $u->name,
                        'symbol'               => $u->symbol,
                        'price'                => $sellPrice,
                        'discount_amount'      => $discountAmount,
                        'price_after_discount' => $netPrice,
                        'tax_name'             => $product->tax?->name,
                        'tax_rate'             => $product->tax?->rate,
                        'tax_type'             => $product->tax?->type,
                        'tax_amount'           => $taxAmount,
                        'price_with_tax'       => round($netPrice + $taxAmount, 2),
                        'available_quantity'   => (int) $stockInThisUnit,
                    ];
                })->values()->toArray();
            }

            return [
                'id'              => $product->id,
                'name'            => $product->name,
                'image'           => $product->image ? asset('storage/' . $product->image) : null,
                'category'        => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'selling_price'   => $product->selling_price,
                'discount'        => $product->discount,
                'discount_type'   => $product->discount_type,
                'net_price'       => $product->net_price,
                'final_price'     => $product->final_price,
                'unit'            => $unit ? ['id' => $unit->id, 'name' => $unit->name, 'symbol' => $unit->symbol] : null,
                'tax'             => $product->tax ? [
                    'id'   => $product->tax->id,
                    'name' => $product->tax->name,
                    'rate' => $product->tax->rate,
                    'type' => $product->tax->type,
                ] : null,
                'available_quantity' => (int) $stockInBaseUnit,
                'available_units' => $availableUnits,
            ];
        });

        return $this->successResponse($products, 'تم جلب منتجات الرحلة بنجاح');
    }
}
