<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Category;
use App\Models\InventoryDispatch;
use App\Models\SaleOrderItem;
use App\Models\SaleReturnItem;
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
        $delegate = $request->user();

        // Verify the category is actually assigned to this delegate
        $assigned = $delegate->categories()->where('categories.id', $category->id)->exists();

        if (!$assigned) {
            return $this->forbiddenResponse('هذه الفئة غير مخصصة لك');
        }

        // Find delegate's active trip to show trip-based stock
        $trip = Trip::where('delegate_id', $delegate->id)
            ->whereIn('status', ['active', 'in_transit'])
            ->latest()
            ->first();

        $products = $category->products()
            ->where('is_active', true)
            ->with([
                'unit.derivedUnits',
                'unit.baseUnit.derivedUnits',
                'tax:id,name,rate,type',
                'category:id,name',
            ])
            ->select('id', 'name', 'image', 'category_id', 'unit_id', 'tax_id', 'selling_price', 'discount', 'discount_type', 'net_price', 'final_price')
            ->get();

        if ($trip) {
            // Compute trip-based available stock: dispatched - sold + customer_returns
            $productIds = $products->pluck('id')->toArray();

            // Dispatched in this trip (trip_id match OR delegate dispatches during trip period without trip_id)
            $dispatches = InventoryDispatch::where('delegate_id', $delegate->id)
                ->where(function ($q) use ($trip) {
                    $q->where('trip_id', $trip->id)
                      ->orWhere(function ($q2) use ($trip) {
                          $q2->whereNull('trip_id')
                             ->whereDate('date', '>=', $trip->start_date ?? $trip->created_at->toDateString())
                             ->whereDate('date', '<=', now()->toDateString());
                      });
                })
                ->with('items.unit')
                ->get();

            $dispatchedRaw = [];
            foreach ($dispatches as $dispatch) {
                foreach ($dispatch->items as $item) {
                    if (!in_array($item->product_id, $productIds)) continue;
                    $dispatchedRaw[$item->product_id][] = [
                        'qty'    => (float) $item->quantity,
                        'factor' => $item->unit ? (float) $item->unit->conversion_factor : null,
                    ];
                }
            }

            // Sold (non-cancelled)
            $soldRows = SaleOrderItem::whereHas('order', fn($q) => $q->where('trip_id', $trip->id)->whereNotIn('status', ['cancelled']))
                ->whereIn('product_id', $productIds)
                ->with('unit')
                ->get()
                ->groupBy('product_id');

            // Cancelled orders — quantities returned back to delegate
            $cancelledRows = SaleOrderItem::whereHas('order', fn($q) => $q->where('trip_id', $trip->id)->where('status', 'cancelled'))
                ->whereIn('product_id', $productIds)
                ->with('unit')
                ->get()
                ->groupBy('product_id');

            // Returned from customers (non-cancelled)
            $returnedRows = SaleReturnItem::whereHas('saleReturn', fn($q) => $q->where('trip_id', $trip->id)->whereNotIn('status', ['cancelled']))
                ->whereIn('product_id', $productIds)
                ->with('unit')
                ->get()
                ->groupBy('product_id');

            $products->each(function ($product) use ($dispatchedRaw, $soldRows, $cancelledRows, $returnedRows) {
                $pf      = $product->unit ? (float) $product->unit->conversion_factor : 1.0;
                $convert = fn(float $qty, ?float $uf) => $pf > 0 ? $qty * ($uf ?? $pf) / $pf : $qty;

                $dispatched = array_sum(array_map(
                    fn($e) => $convert((float)$e['qty'], $e['factor']),
                    $dispatchedRaw[$product->id] ?? []
                ));

                $sold = $soldRows->get($product->id, collect())->sum(function ($r) use ($pf, $convert) {
                    $uf = $r->unit ? (float) $r->unit->conversion_factor : $pf;
                    return $convert((float) $r->quantity, $uf);
                });

                $cancelled = $cancelledRows->get($product->id, collect())->sum(function ($r) use ($pf, $convert) {
                    $uf = $r->unit ? (float) $r->unit->conversion_factor : $pf;
                    return $convert((float) $r->quantity, $uf);
                });

                $returned = $returnedRows->get($product->id, collect())->sum(function ($r) use ($pf, $convert) {
                    $uf = $r->unit ? (float) $r->unit->conversion_factor : $pf;
                    return $convert((float) $r->quantity, $uf);
                });

                $product->available_stock = max(0.0, $dispatched - $sold + $cancelled + $returned);
            });
        } else {
            // No active trip — show branch stock
            $branchIds = $delegate->branches()->pluck('branches.id');
            $products->load(['branches' => fn($q) => $q->whereIn('branch_id', $branchIds)]);
            $products->each(function ($product) {
                $product->available_stock = (float) $product->branches->sum('pivot.quantity');
            });
        }

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

        // Collect dispatched quantities: trip_id matches OR (delegate match + dispatched during trip period)
        $dispatches = InventoryDispatch::where('delegate_id', $delegate->id)
            ->where(function ($q) use ($trip) {
                $q->where('trip_id', $trip->id)
                  ->orWhere(function ($q2) use ($trip) {
                      $q2->whereNull('trip_id')
                         ->whereDate('date', '>=', $trip->start_date ?? $trip->created_at->toDateString())
                         ->whereDate('date', '<=', now()->toDateString());
                  });
            })
            ->with('items.unit')
            ->get();

        // [product_id => [['qty' => float, 'factor' => float|null]]]
        $dispatchedRaw = [];
        foreach ($dispatches as $dispatch) {
            foreach ($dispatch->items as $item) {
                $dispatchedRaw[$item->product_id][] = [
                    'qty'    => (float) $item->quantity,
                    'factor' => $item->unit ? (float) $item->unit->conversion_factor : null,
                ];
            }
        }

        if (empty($dispatchedRaw)) {
            return $this->successResponse([], 'لا توجد منتجات في هذه الرحلة');
        }

        $productIds = array_keys($dispatchedRaw);

        // Sold qty per product (non-cancelled sale orders on this trip), in dispatch-item units
        $soldInBase = SaleOrderItem::whereHas('order', fn($q) => $q->where('trip_id', $trip->id)->whereNotIn('status', ['cancelled']))
            ->whereIn('product_id', $productIds)
            ->with('unit')
            ->get()
            ->groupBy('product_id');

        // Cancelled sale order items (quantities returned back to delegate)
        $cancelledInBase = SaleOrderItem::whereHas('order', fn($q) => $q->where('trip_id', $trip->id)->where('status', 'cancelled'))
            ->whereIn('product_id', $productIds)
            ->with('unit')
            ->get()
            ->groupBy('product_id');

        // Returned qty per product (non-cancelled sale returns on this trip)
        $returnedInBase = SaleReturnItem::whereHas('saleReturn', fn($q) => $q->where('trip_id', $trip->id)->whereNotIn('status', ['cancelled']))
            ->whereIn('product_id', $productIds)
            ->with('unit')
            ->get()
            ->groupBy('product_id');

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
            ->each(function ($product) use ($dispatchedRaw, $soldInBase, $cancelledInBase, $returnedInBase) {
                $pf = $product->unit ? (float) $product->unit->conversion_factor : 1.0;

                // Helper: convert qty from any unit factor to product's unit
                $convert = fn(float $qty, ?float $uf) => $pf > 0 ? $qty * ($uf ?? $pf) / $pf : $qty;

                // Dispatched in product unit
                $dispatched = 0.0;
                foreach ($dispatchedRaw[$product->id] ?? [] as $entry) {
                    $dispatched += $convert((float) $entry['qty'], $entry['factor']);
                }

                // Sold in product unit
                $sold = 0.0;
                foreach ($soldInBase->get($product->id, collect()) as $row) {
                    $uf = $row->unit ? (float) $row->unit->conversion_factor : $pf;
                    $sold += $convert((float) $row->quantity, $uf);
                }

                // Cancelled orders — quantities returned back to delegate
                $cancelled = 0.0;
                foreach ($cancelledInBase->get($product->id, collect()) as $row) {
                    $uf = $row->unit ? (float) $row->unit->conversion_factor : $pf;
                    $cancelled += $convert((float) $row->quantity, $uf);
                }

                // Returned from customers (back to delegate's car) in product unit
                $returned = 0.0;
                foreach ($returnedInBase->get($product->id, collect()) as $row) {
                    $uf = $row->unit ? (float) $row->unit->conversion_factor : $pf;
                    $returned += $convert((float) $row->quantity, $uf);
                }

                $product->available_stock = max(0.0, $dispatched - $sold + $cancelled + $returned);
            });

        return $this->successResponse(ProductResource::collection($products)->resolve(), 'تم جلب منتجات الرحلة بنجاح');
    }

    /**
     * List All Products
     *
     * Returns all active products (no stock filter). For display / catalogue purposes.
     *
     * @group Reference Data
     *
     * @queryParam category_id integer optional Filter by category ID. Example: 2
     * @queryParam search string optional Search by product name. Example: بن
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب المنتجات بنجاح",
     *   "data": [{"id": 1, "name": "منتج A", "selling_price": 100}],
     *   "code": 200
     * }
     */
    public function allProducts(Request $request): JsonResponse
    {
        $query = \App\Models\Product::where('is_active', true)
            ->with([
                'unit.derivedUnits',
                'unit.baseUnit.derivedUnits',
                'tax:id,name,rate,type',
                'category:id,name',
            ]);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $products = $query->orderBy('name')->get()
            ->each(fn ($p) => $p->available_stock = 0);

        return $this->successResponse(ProductResource::collection($products)->resolve(), 'تم جلب المنتجات بنجاح');
    }
}
