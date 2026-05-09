<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCollectionRequest;
use App\Http\Resources\Api\CollectionResource;
use App\Models\SaleOrder;
use App\Models\Trip;
use App\Services\CollectionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CollectionService $collectionService) {}

    /**
     * List Collections
     *
     * Returns all collections for the authenticated delegate, optionally filtered by trip.
     *
     * @group Collections
     */
    public function index(Request $request, $tripId = null): JsonResponse
    {
        $delegate = $request->user();

        if ($tripId !== null) {
            try {
                $trip = Trip::findOrFail($tripId);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('الرحلة غير موجودة');
            }

            if ($trip->delegate_id !== $delegate->id) {
                return $this->forbiddenResponse('هذه الرحلة لا تخصك');
            }
        }

        $collections = $this->collectionService->getDelegateCollections($delegate->id, $tripId ? (int) $tripId : null);

        return $this->successResponse(CollectionResource::collection($collections)->resolve(), 'تم جلب التحصيلات بنجاح');
    }

    /**
     * Create Collection
     *
     * Records a cash collection from a customer. Trip is optional — auto-resolved from the linked
     * sale order or the delegate's active trip if not provided.
     *
     * @group Collections
     */
    public function store(StoreCollectionRequest $request, $tripId = null): JsonResponse
    {
        $delegate  = $request->user();
        $validated = $request->validated();

        // Resolve trip
        if ($tripId !== null) {
            try {
                $trip = Trip::findOrFail($tripId);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('الرحلة غير موجودة');
            }

            if ($trip->delegate_id !== $delegate->id) {
                return $this->forbiddenResponse('هذه الرحلة لا تخصك');
            }
        } else {
            // Try to resolve from a linked sale order
            $firstOrderId = collect($validated['items'])->whereNotNull('sale_order_id')->first()['sale_order_id'] ?? null;

            if ($firstOrderId) {
                $order = SaleOrder::find($firstOrderId);
                $trip  = $order?->trip_id ? Trip::find($order->trip_id) : null;
            }

            // Fall back to delegate's active trip
            if (empty($trip)) {
                $trip = Trip::where('delegate_id', $delegate->id)
                    ->whereIn('status', ['active', 'in_transit', 'returning'])
                    ->orderByDesc('id')
                    ->first();
            }
        }

        if (!$trip || !in_array($trip->status, ['active', 'in_transit', 'returning'])) {
            return $this->errorResponse('لا يمكن إنشاء تحصيل — لا توجد رحلة نشطة');
        }

        // Resolve customer_id — from request or from the linked sale order
        $customerId = $validated['customer_id'] ?? null;
        if (!$customerId) {
            $firstOrderId = collect($validated['items'])->whereNotNull('sale_order_id')->first()['sale_order_id'] ?? null;
            if ($firstOrderId) {
                $customerId = SaleOrder::find($firstOrderId)?->customer_id;
            }
        }

        $data = [
            'delegate_id'     => $delegate->id,
            'customer_id'     => $customerId,
            'branch_id'       => $trip->branch_id,
            'treasury_id'     => null,
            'admin_id'        => null,
            'trip_id'         => $trip->id,
            'collection_date' => now()->toDateString(),
            'notes'           => $validated['notes'] ?? null,
        ];

        $collection = $this->collectionService->create($data, $validated['items']);

        $trip->syncTotals();

        return $this->successResponse(CollectionResource::make($collection)->resolve(), 'تم تسجيل التحصيل بنجاح', 201);
    }

    /**
     * Get Collection
     *
     * Returns detailed information about a single collection record.
     *
     * @group Collections
     */
    public function show(Request $request, int $collectionId): JsonResponse
    {
        try {
            $collection = $this->collectionService->getById($collectionId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('التحصيل غير موجود');
        }

        if ($collection->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا التحصيل لا يخصك');
        }

        return $this->successResponse(CollectionResource::make($collection)->resolve(), 'تم جلب تفاصيل التحصيل بنجاح');
    }
}


    /**
     * List Collections
     *
     * Returns all collections recorded during a specific trip.
     *
     * @group Collections
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب التحصيلات بنجاح",
     *   "data": [{"id": 1, "collection_number": "COL-001", "total_amount": 300, "status": "confirmed"}],
     *   "code": 200
     * }
     */
    public function index(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $collections = $this->collectionService->getByTrip($tripId, $request->user()->id);

        return $this->successResponse(CollectionResource::collection($collections)->resolve(), 'تم جلب التحصيلات بنجاح');
    }

    /**
     * Create Collection
     *
     * Records a cash collection from a customer during a trip.
     * Each item can be linked to a specific sale order. The sale order's `paid_amount` is updated automatically.
     *
     * @group Collections
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @bodyParam customer_id integer required Customer ID. Example: 3
     * @bodyParam notes string nullable General notes. Example: تحصيل جزئي
     * @bodyParam items array required List of collection items.
     * @bodyParam items[].sale_order_id integer nullable The sale order this payment is for. Example: 5
     * @bodyParam items[].amount number required Amount collected. Example: 300
     * @bodyParam items[].notes string nullable Per-item notes.
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم تسجيل التحصيل بنجاح",
     *   "data": {"id": 1, "collection_number": "COL-001", "total_amount": 300},
     *   "code": 201
     * }
     * @response 400 scenario="Invalid trip status" {"status": false, "message": "لا يمكن إنشاء تحصيل لهذه الرحلة في وضعها الحالي", "data": null, "code": 400}
     */
    public function store(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        if (!in_array($trip->status, ['active', 'in_transit', 'returning'])) {
            return $this->errorResponse('لا يمكن إنشاء تحصيل لهذه الرحلة في وضعها الحالي');
        }

        $validated = $request->validate([
            'customer_id'             => ['required', 'integer', 'exists:customers,id'],
            'notes'                   => ['nullable', 'string'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.sale_order_id'   => ['nullable', 'integer', 'exists:sale_orders,id'],
            'items.*.amount'          => ['required', 'numeric', 'min:0.01'],
            'items.*.notes'           => ['nullable', 'string'],
        ]);

        $data = [
            'delegate_id'     => $request->user()->id,
            'customer_id'     => $validated['customer_id'],
            'branch_id'       => $trip->branch_id,
            'treasury_id'     => null,
            'admin_id'        => null,
            'trip_id'         => $trip->id,
            'collection_date' => now()->toDateString(),
            'notes'           => $validated['notes'] ?? null,
        ];

        $collection = $this->collectionService->create($data, $validated['items']);

        // Sync trip totals
        $trip->syncTotals();

        return $this->successResponse(CollectionResource::make($collection)->resolve(), 'تم تسجيل التحصيل بنجاح', 201);
    }

    /**
     * Get Collection
     *
     * Returns detailed information about a single collection record.
     *
     * @group Collections
     *
     * @urlParam collection integer required The collection ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل التحصيل بنجاح",
     *   "data": {"id": 1, "collection_number": "COL-001", "items": []},
     *   "code": 200
     * }
     */
    public function show(Request $request, int $collectionId): JsonResponse
    {
        $collection = $this->collectionService->getById($collectionId);

        if ($collection->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا التحصيل لا يخصك');
        }

        return $this->successResponse(CollectionResource::make($collection)->resolve(), 'تم جلب تفاصيل التحصيل بنجاح');
    }

    /**
     * My Collections
     *
     * Returns all collections made by the authenticated delegate with optional filters.
     *
     * @group Collections
     *
     * @queryParam customer_name string Filter by customer name (partial match). Example: أحمد
     * @queryParam collection_number string Filter by collection number (partial match). Example: COL-20260501
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب التحصيلات بنجاح",
     *   "data": [{"id": 1, "collection_number": "COL-20260501-0001", "total_amount": 500, "customer": {"id": 3, "name": "أحمد"}}],
     *   "code": 200
     * }
     */
    public function myCollections(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name'     => ['nullable', 'string', 'max:100'],
            'collection_number' => ['nullable', 'string', 'max:50'],
        ]);

        $query = Collection::with(['customer', 'items.saleOrder'])
            ->where('delegate_id', $request->user()->id);

        if ($request->filled('customer_name')) {
            $query->whereHas('customer', fn ($q) =>
                $q->where('name', 'like', '%' . $request->customer_name . '%')
            );
        }

        if ($request->filled('collection_number')) {
            $query->where('collection_number', 'like', '%' . $request->collection_number . '%');
        }

        $collections = $query->latest()->get();

        return $this->successResponse(CollectionResource::collection($collections)->resolve(), 'تم جلب التحصيلات بنجاح');
    }
}
