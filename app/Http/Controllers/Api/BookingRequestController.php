<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripBookingRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingRequestController extends Controller
{
    use ApiResponse;

    /**
     * List Booking Requests
     *
     * Returns booking requests for a specific trip.
     *
     * @group Booking Requests
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب طلبات الحجز بنجاح",
     *   "data": [{"id": 1, "customer_name": "عميل A", "status": "pending"}],
     *   "code": 200
     * }
     */
    public function index(Request $request, int $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $requests = TripBookingRequest::where('trip_id', $tripId)
            ->where('delegate_id', $request->user()->id)
            ->with(['items.product:id,name,image', 'items.unit:id,name,symbol'])
            ->latest()
            ->get()
            ->map(fn($r) => $this->formatRequest($r));

        return $this->successResponse($requests, 'تم جلب طلبات الحجز بنجاح');
    }

    /**
     * Create Booking Request
     *
     * Creates a booking request (potential future sale) during an active trip.
     * Status flow: `pending` → `confirmed` → `converted` (to sale order) or `cancelled`.
     *
     * @group Booking Requests
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @bodyParam customer_name string required Customer name. Example: عميل جديد
     * @bodyParam customer_phone string nullable Customer phone. Example: 0501234567
     * @bodyParam customer_address string nullable Customer address. Example: شارع الجامعة
     * @bodyParam notes string nullable Notes. Example: يريد تسليم مساء
     * @bodyParam items array required List of products to book.
     * @bodyParam items[].product_id integer required Product ID. Example: 5
     * @bodyParam items[].quantity number required Quantity. Example: 2
     * @bodyParam items[].unit_id integer nullable Unit ID. Example: 1
     * @bodyParam items[].unit_price number required Unit price. Example: 100
     * @bodyParam items[].notes string nullable Per-item notes.
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم إنشاء طلب الحجز بنجاح",
     *   "data": {"id": 1, "customer_name": "عميل جديد", "status": "pending"},
     *   "code": 201
     * }
     * @response 400 scenario="Trip not active" {"status": false, "message": "لا يمكن إنشاء طلب حجز لرحلة غير نشطة", "data": null, "code": 400}
     */
    public function store(Request $request, int $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        if (!in_array($trip->status, ['active', 'in_transit'])) {
            return $this->errorResponse('لا يمكن إنشاء طلب حجز لرحلة غير نشطة');
        }

        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_phone'   => ['nullable', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_id'    => ['nullable', 'integer', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes'      => ['nullable', 'string'],
        ]);

        $bookingRequest = TripBookingRequest::create([
            'trip_id'          => $tripId,
            'delegate_id'      => $request->user()->id,
            'customer_name'    => $validated['customer_name'],
            'customer_phone'   => $validated['customer_phone'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'status'           => 'pending',
        ]);

        foreach ($validated['items'] as $item) {
            $bookingRequest->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_id'    => $item['unit_id'] ?? null,
                'unit_price' => $item['unit_price'],
                'notes'      => $item['notes'] ?? null,
            ]);
        }

        $bookingRequest->load(['items.product:id,name', 'items.unit:id,name,symbol']);

        return $this->successResponse($this->formatRequest($bookingRequest), 'تم إنشاء طلب الحجز بنجاح', 201);
    }

    /**
     * Get Booking Request
     *
     * Returns a single booking request with its items and linked sale order (if converted).
     *
     * @group Booking Requests
     *
     * @urlParam bookingRequest integer required The booking request ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل طلب الحجز بنجاح",
     *   "data": {"id": 1, "status": "pending", "items": []},
     *   "code": 200
     * }
     */
    public function show(Request $request, int $requestId): JsonResponse
    {
        $bookingRequest = TripBookingRequest::with([
            'items.product:id,name,image',
            'items.unit:id,name,symbol',
            'convertedOrder:id,order_number,status,total',
        ])->findOrFail($requestId);

        if ($bookingRequest->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا الطلب لا يخصك');
        }

        return $this->successResponse($this->formatRequest($bookingRequest, detailed: true), 'تم جلب تفاصيل طلب الحجز بنجاح');
    }

    /**
     * Cancel Booking Request
     *
     * Cancels a `pending` booking request. Cannot cancel if already confirmed or converted.
     *
     * @group Booking Requests
     *
     * @urlParam bookingRequest integer required The booking request ID. Example: 1
     *
     * @response 200 scenario="Cancelled" {"status": true, "message": "تم إلغاء طلب الحجز بنجاح", "data": null, "code": 200}
     * @response 400 scenario="Not pending" {"status": false, "message": "لا يمكن إلغاء هذا الطلب في وضعه الحالي", "data": null, "code": 400}
     */
    public function cancel(Request $request, int $requestId): JsonResponse
    {
        $bookingRequest = TripBookingRequest::findOrFail($requestId);

        if ($bookingRequest->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا الطلب لا يخصك');
        }

        if ($bookingRequest->status !== 'pending') {
            return $this->errorResponse('لا يمكن إلغاء هذا الطلب في وضعه الحالي');
        }

        $bookingRequest->update(['status' => 'cancelled']);

        return $this->successResponse(null, 'تم إلغاء طلب الحجز بنجاح');
    }

    private function formatRequest(TripBookingRequest $r, bool $detailed = false): array
    {
        $data = [
            'id'               => $r->id,
            'trip_id'          => $r->trip_id,
            'customer_name'    => $r->customer_name,
            'customer_phone'   => $r->customer_phone,
            'customer_address' => $r->customer_address,
            'notes'            => $r->notes,
            'status'           => $r->status,
            'status_label'     => $r->statusLabel(),
            'created_at'       => $r->created_at,
            'items'            => $r->items->map(fn($item) => [
                'id'         => $item->id,
                'product'    => $item->product ? ['id' => $item->product->id, 'name' => $item->product->name] : null,
                'unit'       => $item->unit ? ['id' => $item->unit->id, 'name' => $item->unit->name] : null,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal'   => $item->subtotal,
                'notes'      => $item->notes,
            ])->values(),
        ];

        if ($detailed && $r->convertedOrder) {
            $data['converted_order'] = [
                'id'           => $r->convertedOrder->id,
                'order_number' => $r->convertedOrder->order_number,
                'status'       => $r->convertedOrder->status,
                'total'        => $r->convertedOrder->total,
            ];
        }

        return $data;
    }
}
