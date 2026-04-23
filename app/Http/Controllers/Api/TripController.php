<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TripService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TripService $tripService) {}

    /**
     * List Trips
     *
     * Returns all trips belonging to the authenticated delegate.
     *
     * @group Trips
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب الرحلات بنجاح",
     *   "data": [{"id": 1, "trip_number": "TRIP-001", "status": "draft", "status_label": "مسودة"}],
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegate = $request->user();
        $trips = $this->tripService->getByDelegate($delegate->id);

        $data = $trips->map(fn($trip) => [
            'id'                    => $trip->id,
            'trip_number'           => $trip->trip_number,
            'status'                => $trip->status,
            'status_label'          => $trip->statusLabel(),
            'start_date'            => $trip->start_date,
            'expected_return_date'  => $trip->expected_return_date,
            'actual_return_date'    => $trip->actual_return_date,
            'branch'                => $trip->branch ? ['id' => $trip->branch->id, 'name' => $trip->branch->name] : null,
            'total_dispatched_value' => $trip->total_dispatched_value,
            'total_invoiced'        => $trip->total_invoiced,
            'total_collected'       => $trip->total_collected,
            'total_returned_value'  => $trip->total_returned_value,
            'cash_custody_amount'   => $trip->cash_custody_amount,
        ]);

        return $this->successResponse($data, 'تم جلب الرحلات بنجاح');
    }

    /**
     * Create Trip
     *
     * Creates a new trip in `draft` status for the delegate. The branch must be assigned to the delegate.
     *
     * @group Trips
     *
     * @bodyParam branch_id integer required The ID of an assigned branch. Example: 1
     * @bodyParam expected_return_date string nullable Expected return date (YYYY-MM-DD). Must be today or later. Example: 2026-05-01
     * @bodyParam notes string nullable Optional notes. Example: الرحلة الأسبوعية
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم إنشاء الرحلة بنجاح",
     *   "data": {"id": 1, "trip_number": "TRIP-001", "status": "draft"},
     *   "code": 201
     * }
     * @response 403 scenario="Unassigned branch" {"status": false, "message": "هذا الفرع غير مخصص لك", "data": null, "code": 403}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'            => ['required', 'integer', 'exists:branches,id'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes'                => ['nullable', 'string'],
        ]);

        $delegate = $request->user();

        // Verify the branch is assigned to this delegate
        $branchIds = $delegate->branches()->pluck('branches.id');
        if (!$branchIds->contains($validated['branch_id'])) {
            return $this->forbiddenResponse('هذا الفرع غير مخصص لك');
        }

        $trip = $this->tripService->create(array_merge($validated, ['delegate_id' => $delegate->id]));

        return $this->successResponse($this->formatTrip($trip), 'تم إنشاء الرحلة بنجاح', 201);
    }

    /**
     * Get Trip
     *
     * Returns detailed information about a single trip.
     *
     * @group Trips
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل الرحلة بنجاح",
     *   "data": {"id": 1, "trip_number": "TRIP-001", "status": "active", "branch": {"id": 1, "name": "الفرع الرئيسي"}},
     *   "code": 200
     * }
     * @response 403 scenario="Not your trip" {"status": false, "message": "هذه الرحلة لا تخصك", "data": null, "code": 403}
     */
    public function show(Request $request, int $tripId): JsonResponse
    {
        $trip = $this->tripService->getById($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        return $this->successResponse($this->formatTrip($trip, detailed: true), 'تم جلب تفاصيل الرحلة بنجاح');
    }

    /**
     * Start Trip
     *
     * Transitions a trip from `draft` (or `returning`) to `active`.
     *
     * @group Trips
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {"status": true, "message": "تم تشغيل الرحلة بنجاح", "data": {"status": "active"}, "code": 200}
     * @response 400 scenario="Invalid status" {"status": false, "message": "لا يمكن تشغيل الرحلة بحالتها الحالية", "data": null, "code": 400}
     */
    public function start(Request $request, int $tripId): JsonResponse
    {
        $trip = $this->tripService->getById($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $trip = $this->tripService->start($trip);

        return $this->successResponse($this->formatTrip($trip), 'تم تشغيل الرحلة بنجاح');
    }

    /**
     * End Trip
     *
     * Transitions an `active` (or `in_transit`) trip to `returning` and syncs totals.
     *
     * @group Trips
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {"status": true, "message": "تم إنهاء الرحلة بنجاح", "data": {"status": "returning"}, "code": 200}
     * @response 400 scenario="Invalid status" {"status": false, "message": "لا يمكن إنهاء الرحلة بحالتها الحالية", "data": null, "code": 400}
     */
    public function end(Request $request, int $tripId): JsonResponse
    {
        $trip = $this->tripService->getById($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $trip = $this->tripService->end($trip);

        return $this->successResponse($this->formatTrip($trip), 'تم إنهاء الرحلة بنجاح');
    }

    private function formatTrip($trip, bool $detailed = false): array
    {
        $data = [
            'id'                     => $trip->id,
            'trip_number'            => $trip->trip_number,
            'status'                 => $trip->status,
            'status_label'           => $trip->statusLabel(),
            'start_date'             => $trip->start_date,
            'expected_return_date'   => $trip->expected_return_date,
            'actual_return_date'     => $trip->actual_return_date,
            'notes'                  => $trip->notes,
            'branch'                 => $trip->branch ? ['id' => $trip->branch->id, 'name' => $trip->branch->name] : null,
            'total_dispatched_value' => $trip->total_dispatched_value,
            'total_invoiced'         => $trip->total_invoiced,
            'total_collected'        => $trip->total_collected,
            'total_returned_value'   => $trip->total_returned_value,
            'outstanding'            => $trip->outstanding,
            'cash_custody_amount'    => $trip->cash_custody_amount,
            'cash_custody_note'      => $trip->cash_custody_note,
        ];

        if ($detailed) {
            $data['settlement'] = [
                'status'          => $trip->settlement_status,
                'cash_expected'   => $trip->settlement_cash_expected,
                'cash_actual'     => $trip->settlement_cash_actual,
                'cash_deficit'    => $trip->settlement_cash_deficit,
                'product_deficit' => $trip->settlement_product_deficit,
                'notes'           => $trip->settlement_notes,
            ];
        }

        return $data;
    }
}
