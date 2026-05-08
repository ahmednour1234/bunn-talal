<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSaleReturnRequest;
use App\Http\Resources\Api\SaleReturnResource;
use App\Models\SaleOrder;
use App\Models\Trip;
use App\Services\SaleReturnService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SaleReturnService $saleReturnService) {}

    /**
     * List Sale Returns
     *
     * Returns all sale returns recorded during a specific trip.
     *
     * @group Sale Returns
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب المرتجعات بنجاح",
     *   "data": [{"id": 1, "return_number": "RET-001", "status": "confirmed", "total": 200}],
     *   "code": 200
     * }
     */
    public function index(Request $request, $tripId = null): JsonResponse
    {
        $delegate = $request->user();

        if ($tripId !== null) {
            $trip = Trip::findOrFail($tripId);

            if ($trip->delegate_id !== $delegate->id) {
                return $this->forbiddenResponse('هذه الرحلة لا تخصك');
            }
        }

        $returns = $this->saleReturnService->getDelegateReturns($delegate->id, $tripId ? (int) $tripId : null);

        return $this->successResponse(SaleReturnResource::collection($returns)->resolve(), 'تم جلب المرتجعات بنجاح');
    }

    /**
     * Create Sale Return
     *
     * Creates a return against an existing sale order during a trip.
     * The delegate must own the original sale order.
     *
     * @group Sale Returns
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @bodyParam sale_order_id integer required The original sale order ID. Example: 1
     * @bodyParam notes string nullable Notes. Example: منتج تالف
     * @bodyParam items array required Items being returned.
     * @bodyParam items[].product_id integer required Product ID. Example: 1
     * @bodyParam items[].unit_id integer nullable Unit ID. Example: 1
     * @bodyParam items[].quantity number required Quantity to return. Example: 2
     * @bodyParam items[].unit_price number required Price per unit. Example: 100
     * @bodyParam items[].reason string nullable Return reason. Example: تالف
     * @bodyParam items[].sale_order_item_id integer nullable The original order item ID. Example: 5
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم إنشاء المرتجع بنجاح",
     *   "data": {"id": 1, "return_number": "RET-001", "total": 200},
     *   "code": 201
     * }
     * @response 400 scenario="Invalid trip status" {"status": false, "message": "لا يمكن إنشاء مرتجع لهذه الرحلة في وضعها الحالي", "data": null, "code": 400}
     */
    public function store(StoreSaleReturnRequest $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        if (!in_array($trip->status, ['active', 'in_transit', 'returning'])) {
            return $this->errorResponse('لا يمكن إنشاء مرتجع لهذه الرحلة في وضعها الحالي');
        }

        $validated = $request->validated();

        // Verify the order belongs to this delegate
        $order = SaleOrder::findOrFail($validated['sale_order_id']);
        if ($order->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الفاتورة لا تخصك');
        }

        $returnData = [
            'sale_order_id' => $validated['sale_order_id'],
            'customer_id'   => $order->customer_id,
            'branch_id'     => $trip->branch_id,
            'admin_id'      => null,
            'treasury_id'   => null,
            'trip_id'       => $trip->id,
            'date'          => now()->toDateString(),
            'notes'         => $validated['notes'] ?? null,
        ];

        $return = $this->saleReturnService->createReturn($returnData, $validated['items']);

        // Sync trip totals
        $trip->syncTotals();

        return $this->successResponse(SaleReturnResource::make($return)->resolve(), 'تم إنشاء المرتجع بنجاح', 201);
    }

    /**
     * Get Sale Return
     *
     * Returns a single sale return with all items and the linked sale order.
     *
     * @group Sale Returns
     *
     * @urlParam return integer required The return ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل المرتجع بنجاح",
     *   "data": {"id": 1, "return_number": "RET-001", "items": []},
     *   "code": 200
     * }
     */
    public function show(Request $request, int $returnId): JsonResponse
    {
        $return = $this->saleReturnService->getById($returnId);

        // Verify the return's sale order belongs to this delegate
        if ($return->order && $return->order->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا المرتجع لا يخصك');
        }

        return $this->successResponse(SaleReturnResource::make($return)->resolve(), 'تم جلب تفاصيل المرتجع بنجاح');
    }
}

