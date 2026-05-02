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
            ->with([
                'unit.derivedUnits',
                'unit.baseUnit.derivedUnits',
                'tax:id,name,rate,type',
            ])
            ->select('id', 'name', 'image', 'category_id', 'unit_id', 'tax_id', 'selling_price', 'discount', 'discount_type')
            ->get()
            ->map(function ($product) {
                $unit           = $product->unit;
                $availableUnits = [];

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

                    $availableUnits = $familyUnits->map(function ($u) use ($product, $productFactor) {
                        $factor    = (float) $u->conversion_factor / $productFactor;
                        $sellPrice = round((float) $product->selling_price * $factor, 2);

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
                            'id'                 => $u->id,
                            'name'               => $u->name,
                            'symbol'             => $u->symbol,
                            'price'              => $sellPrice,
                            'discount_amount'    => $discountAmount,
                            'price_after_discount' => $netPrice,
                            'tax_name'           => $product->tax?->name,
                            'tax_rate'           => $product->tax?->rate,
                            'tax_type'           => $product->tax?->type,
                            'tax_amount'         => $taxAmount,
                            'price_with_tax'     => round($netPrice + $taxAmount, 2),
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
}
