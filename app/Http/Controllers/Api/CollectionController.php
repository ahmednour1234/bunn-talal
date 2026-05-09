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
