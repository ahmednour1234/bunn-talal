<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleOrder;
use App\Models\Trip;
use App\Services\SaleOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleOrderController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SaleOrderService $saleOrderService) {}

    /**
     * List Sale Orders
     *
     * Returns all sale orders created during a specific trip.
     *
     * @group Sale Orders
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب أوامر البيع بنجاح",
     *   "data": [{"id": 1, "order_number": "INV-001", "status": "confirmed", "total": 500}],
     *   "code": 200
     * }
     */
    public function index(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $orders = SaleOrder::where('trip_id', $tripId)
            ->where('delegate_id', $request->user()->id)
            ->with(['customer:id,name,phone', 'items'])
            ->latest()
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        return $this->successResponse($orders, 'تم جلب أوامر البيع بنجاح');
    }

    /**
     * Create Sale Order
     *
     * Creates a new sale order for a customer during an active trip.
     * For `cash`: the full amount is automatically recorded as paid.
     * For `credit`: zero payment is recorded; customer owes full amount.
     * For `partial`: provide `paid_amount` for the upfront payment.
     *
     * @group Sale Orders
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @bodyParam customer_id integer required Customer ID. Example: 3
     * @bodyParam payment_method string required One of: `cash`, `credit`, `partial`. Example: cash
     * @bodyParam discount_amount number nullable Order-level discount value. Example: 50
     * @bodyParam discount_type string nullable `fixed` or `percentage`. Example: fixed
     * @bodyParam due_date string nullable Payment due date (YYYY-MM-DD) for credit orders. Example: 2026-06-01
     * @bodyParam notes string nullable Notes. Example: تسليم سريع
     * @bodyParam paid_amount number nullable Required when payment_method is `partial`. Example: 200
     * @bodyParam items array required Sale items.
     * @bodyParam items[].product_id integer required Product ID. Example: 1
     * @bodyParam items[].unit_id integer nullable Unit ID. Example: 1
     * @bodyParam items[].quantity number required Quantity. Example: 5
     * @bodyParam items[].unit_price number required Price per unit. Example: 100
     * @bodyParam items[].discount number nullable Item discount value. Example: 0
     * @bodyParam items[].discount_type string nullable `fixed` or `percentage`. Example: fixed
     * @bodyParam items[].tax_amount number nullable Tax amount for this item. Example: 15
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم إنشاء فاتورة البيع بنجاح",
     *   "data": {"id": 1, "order_number": "INV-001", "total": 500, "paid_amount": 500},
     *   "code": 201
     * }
     * @response 400 scenario="Trip not active" {"status": false, "message": "لا يمكن إنشاء فاتورة بيع لرحلة غير نشطة", "data": null, "code": 400}
     */
    public function store(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        if (!in_array($trip->status, ['active', 'in_transit'])) {
            return $this->errorResponse('لا يمكن إنشاء فاتورة بيع لرحلة غير نشطة');
        }

        $validated = $request->validate([
            'customer_id'          => ['required', 'integer', 'exists:customers,id'],
            'payment_method'       => ['required', 'string', 'in:cash,credit,partial'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_type'        => ['nullable', 'string', 'in:fixed,percentage'],
            'due_date'             => ['nullable', 'date'],
            'notes'                => ['nullable', 'string'],
            'paid_amount'          => ['nullable', 'numeric', 'min:0'],  // for partial method
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'      => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.discount'     => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'items.*.tax_amount'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $delegate = $request->user();

        // Build order data with delegate context
        $orderData = [
            'customer_id'     => $validated['customer_id'],
            'branch_id'       => $trip->branch_id,
            'admin_id'        => null,
            'delegate_id'     => $delegate->id,
            'treasury_id'     => null,
            'trip_id'         => $trip->id,
            'date'            => now()->toDateString(),
            'due_date'        => $validated['due_date'] ?? null,
            'payment_method'  => $validated['payment_method'],
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'discount_type'   => $validated['discount_type'] ?? 'fixed',
            'notes'           => $validated['notes'] ?? null,
        ];

        // Determine initial payment amount
        $initialPayment = null;
        if ($validated['payment_method'] === 'partial' && !empty($validated['paid_amount'])) {
            $initialPayment = (float) $validated['paid_amount'];
        } elseif ($validated['payment_method'] === 'credit') {
            $initialPayment = 0; // No upfront payment for credit
        }
        // For 'cash' - SaleOrderService will pay the full amount automatically

        $order = $this->saleOrderService->createOrder($orderData, $validated['items'], $initialPayment);

        // Sync trip totals
        $trip->syncTotals();

        return $this->successResponse($this->formatOrder($order, detailed: true), 'تم إنشاء فاتورة البيع بنجاح', 201);
    }

    /**
     * Get Sale Order
     *
     * Returns a single sale order with its items and payment history.
     *
     * @group Sale Orders
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل الفاتورة بنجاح",
     *   "data": {"id": 1, "order_number": "INV-001", "items": [], "payments": []},
     *   "code": 200
     * }
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        $order = SaleOrder::with([
            'customer:id,name,phone,email',
            'items.product:id,name,image',
            'items.unit:id,name,symbol',
            'payments',
        ])->findOrFail($orderId);

        if ($order->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الفاتورة لا تخصك');
        }

        return $this->successResponse($this->formatOrder($order, detailed: true), 'تم جلب تفاصيل الفاتورة بنجاح');
    }

    /**
     * Add Payment
     *
     * Records a new payment against a credit or partial-payment order.
     *
     * @group Sale Orders
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @bodyParam amount number required The payment amount. Example: 150
     * @bodyParam notes string nullable Notes about this payment. Example: دفعة جزئية
     *
     * @response 200 scenario="Success" {"status": true, "message": "تم تسجيل الدفعة بنجاح", "data": {"id": 1, "paid_amount": 350}, "code": 200}
     */
    public function addPayment(Request $request, int $orderId): JsonResponse
    {
        $order = SaleOrder::findOrFail($orderId);

        if ($order->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الفاتورة لا تخصك');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes'  => ['nullable', 'string'],
        ]);

        $order = $this->saleOrderService->addPayment($orderId, [
            'amount'   => $validated['amount'],
            'admin_id' => null,
            'notes'    => $validated['notes'] ?? null,
        ]);

        return $this->successResponse($this->formatOrder($order, detailed: true), 'تم تسجيل الدفعة بنجاح');
    }

    /**
     * Cancel Sale Order
     *
     * Cancels a sale order. Cannot cancel if it has already been paid in full.
     *
     * @group Sale Orders
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 scenario="Cancelled" {"status": true, "message": "تم إلغاء فاتورة البيع بنجاح", "data": null, "code": 200}
     */
    public function cancel(Request $request, int $orderId): JsonResponse
    {
        $order = SaleOrder::findOrFail($orderId);

        if ($order->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الفاتورة لا تخصك');
        }

        $order = $this->saleOrderService->cancelOrder($orderId);

        return $this->successResponse(null, 'تم إلغاء فاتورة البيع بنجاح');
    }

    private function formatOrder(SaleOrder $order, bool $detailed = false): array
    {
        $data = [
            'id'               => $order->id,
            'order_number'     => $order->order_number,
            'status'           => $order->status,
            'status_label'     => $order->status_label,
            'payment_method'   => $order->payment_method,
            'payment_method_label' => $order->payment_method_label,
            'date'             => $order->date,
            'due_date'         => $order->due_date,
            'subtotal'         => $order->subtotal,
            'discount_amount'  => $order->discount_amount,
            'discount_type'    => $order->discount_type,
            'tax_amount'       => $order->tax_amount,
            'total'            => $order->total,
            'paid_amount'      => $order->paid_amount,
            'remaining_amount' => $order->remaining_amount,
            'customer'         => $order->customer ? ['id' => $order->customer->id, 'name' => $order->customer->name, 'phone' => $order->customer->phone] : null,
            'notes'            => $order->notes,
            'trip_id'          => $order->trip_id,
        ];

        if ($detailed) {
            $data['items'] = $order->items->map(fn($item) => [
                'id'            => $item->id,
                'product'       => $item->product ? ['id' => $item->product->id, 'name' => $item->product->name] : null,
                'unit'          => $item->unit ? ['id' => $item->unit->id, 'name' => $item->unit->name] : null,
                'quantity'      => $item->quantity,
                'unit_price'    => $item->unit_price,
                'discount'      => $item->discount,
                'discount_type' => $item->discount_type,
                'tax_amount'    => $item->tax_amount,
                'total'         => $item->total,
            ])->values();

            $data['payments'] = $order->payments->map(fn($p) => [
                'id'             => $p->id,
                'amount'         => $p->amount,
                'payment_method' => $p->payment_method,
                'payment_date'   => $p->payment_date,
                'notes'          => $p->notes,
            ])->values();
        }

        return $data;
    }
}
