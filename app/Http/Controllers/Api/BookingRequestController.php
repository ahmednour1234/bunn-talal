<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookingRequestRequest;
use App\Http\Resources\Api\BookingRequestResource;
use App\Services\BookingRequestService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingRequestController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BookingRequestService $service) {}

    /**
     * List Booking Requests
     *
     * Returns booking requests for the delegate, optionally filtered by trip.
     *
     * @group Booking Requests
     *
     * @urlParam trip integer optional The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب طلبات الحجز بنجاح",
     *   "data": [{"id": 1, "customer_name": "عميل A", "status": "pending"}],
     *   "code": 200
     * }
     */
    public function index(Request $request, ?int $tripId = null): JsonResponse
    {
        $requests = $this->service->getDelegateRequests($request->user()->id, $tripId);

        return $this->successResponse(
            BookingRequestResource::collection($requests)->resolve(),
            'تم جلب طلبات الحجز بنجاح'
        );
    }

    /**
     * Create Booking Request
     *
     * Creates a booking request (potential future sale).
     * Trip is optional — auto-resolved from the delegate's active trip if not provided.
     * Unit price is auto-filled from the product's selling price.
     * Customer name is optional.
     *
     * @group Booking Requests
     *
     * @urlParam trip integer optional The trip ID. Example: 1
     *
     * @bodyParam customer_name string nullable Customer name. Example: عميل جديد
     * @bodyParam customer_phone string nullable Customer phone. Example: 0501234567
     * @bodyParam customer_address string nullable Customer address. Example: شارع الجامعة
     * @bodyParam notes string nullable Notes. Example: يريد تسليم مساء
     * @bodyParam items array required List of products to book.
     * @bodyParam items[].product_id integer required Product ID. Example: 5
     * @bodyParam items[].quantity number required Quantity. Example: 2
     * @bodyParam items[].unit_id integer nullable Unit ID. Example: 1
     * @bodyParam items[].notes string nullable Per-item notes.
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم إنشاء طلب الحجز بنجاح",
     *   "data": {"id": 1, "status": "pending"},
     *   "code": 201
     * }
     */
    public function store(StoreBookingRequestRequest $request, ?int $tripId = null): JsonResponse
    {
        $data = $request->validated();

        if ($tripId) {
            $data['trip_id'] = $tripId;
        }

        $booking = $this->service->create($data, $request->user()->id);

        return $this->successResponse(
            BookingRequestResource::make($booking)->resolve(),
            'تم إنشاء طلب الحجز بنجاح',
            201
        );
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
     * @response 404 scenario="Not Found" {"status": false, "message": "طلب الحجز غير موجود", "data": null, "code": 404}
     */
    public function show(Request $request, int $requestId): JsonResponse
    {
        try {
            $booking = $this->service->getById($requestId);
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('طلب الحجز غير موجود');
        }

        if ($booking->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا الطلب لا يخصك');
        }

        return $this->successResponse(
            BookingRequestResource::make($booking)->resolve(),
            'تم جلب تفاصيل طلب الحجز بنجاح'
        );
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
        try {
            $this->service->cancel($requestId, $request->user()->id);
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('طلب الحجز غير موجود');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'forbidden') {
                return $this->forbiddenResponse('هذا الطلب لا يخصك');
            }
            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse(null, 'تم إلغاء طلب الحجز بنجاح');
    }
}
