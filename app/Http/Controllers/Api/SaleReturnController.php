<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
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
    public function index(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $returns = SaleReturn::where('trip_id', $tripId)
            ->with([
                'customer:id,name,phone',
                'items.product:id,name',
                'items.unit:id,name,symbol',
            ])
            ->latest()
            ->get()
            ->map(fn($r) => $this->formatReturn($r));

        return $this->successResponse($returns, 'تم جلب المرتجعات بنجاح');
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
    public function store(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        if (!in_array($trip->status, ['active', 'in_transit', 'returning'])) {
            return $this->errorResponse('لا يمكن إنشاء مرتجع لهذه الرحلة في وضعها الحالي');
        }

        $validated = $request->validate([
            'sale_order_id'        => ['required', 'integer', 'exists:sale_orders,id'],
            'notes'                => ['nullable', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'      => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.reason'       => ['nullable', 'string'],
            'items.*.sale_order_item_id' => ['nullable', 'integer', 'exists:sale_order_items,id'],
        ]);

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

        return $this->successResponse($this->formatReturn($return, detailed: true), 'تم إنشاء المرتجع بنجاح', 201);
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
        $return = SaleReturn::with([
            'customer:id,name,phone',
            'order:id,order_number,total,paid_amount',
            'items.product:id,name',
            'items.unit:id,name,symbol',
        ])->findOrFail($returnId);

        // Verify the return's sale order belongs to this delegate
        if ($return->order && $return->order->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا المرتجع لا يخصك');
        }

        return $this->successResponse($this->formatReturn($return, detailed: true), 'تم جلب تفاصيل المرتجع بنجاح');
    }

    private function formatReturn(SaleReturn $return, bool $detailed = false): array
    {
        $data = [
            'id'            => $return->id,
            'return_number' => $return->return_number,
            'status'        => $return->status,
            'status_label'  => $return->status_label,
            'date'          => $return->date,
            'subtotal'      => $return->subtotal,
            'refund_amount' => $return->refund_amount,
            'customer'      => $return->customer ? ['id' => $return->customer->id, 'name' => $return->customer->name] : null,
            'notes'         => $return->notes,
            'trip_id'       => $return->trip_id,
        ];

        if ($detailed) {
            $data['order'] = $return->order ? ['id' => $return->order->id, 'order_number' => $return->order->order_number] : null;
            $data['items'] = $return->items->map(fn($item) => [
                'id'            => $item->id,
                'product'       => $item->product ? ['id' => $item->product->id, 'name' => $item->product->name] : null,
                'unit'          => $item->unit ? ['id' => $item->unit->id, 'name' => $item->unit->name] : null,
                'quantity'      => $item->quantity,
                'unit_price'    => $item->unit_price,
                'refund_amount' => $item->refund_amount,
                'reason'        => $item->reason,
            ])->values();
        }

        return $data;
    }
}
