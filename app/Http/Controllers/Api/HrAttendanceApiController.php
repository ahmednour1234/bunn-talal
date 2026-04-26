<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\HrAttendanceRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrAttendanceApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HrAttendanceRepositoryInterface $attendanceRepo) {}

    /**
     * List My Attendance
     *
     * Returns a paginated list of the authenticated delegate's attendance records.
     *
     * @group HR - Attendance
     *
     * @queryParam date_from string Filter from date (YYYY-MM-DD). Example: 2026-04-01
     * @queryParam date_to string Filter to date (YYYY-MM-DD). Example: 2026-04-30
     * @queryParam status string Filter by status. One of: present, absent, late, on_leave. Example: present
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب سجلات الحضور بنجاح",
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1, "date": "2026-04-26", "check_in": "08:00:00", "check_out": "17:00:00",
     *         "status": "present", "status_label": "حاضر", "notes": null,
     *         "created_at": "2026-04-26T08:05:00Z"
     *       }
     *     ],
     *     "meta": {"current_page": 1, "last_page": 1, "per_page": 30, "total": 1}
     *   },
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegate = $request->user();

        $filters = $request->only(['date_from', 'date_to', 'status']);

        $records = $this->attendanceRepo->forDelegate($delegate->id, $filters);

        return $this->successResponse([
            'data' => $records->getCollection()->map(fn($a) => $this->formatAttendance($a)),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page'    => $records->lastPage(),
                'per_page'     => $records->perPage(),
                'total'        => $records->total(),
            ],
        ], 'تم جلب سجلات الحضور بنجاح');
    }

    /**
     * Attendance Monthly Summary
     *
     * Returns a count summary of attendance statuses for a given month and year.
     *
     * @group HR - Attendance
     *
     * @queryParam month integer required Month number (1-12). Example: 4
     * @queryParam year integer required Year. Example: 2026
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب ملخص الحضور بنجاح",
     *   "data": {"present": 20, "absent": 2, "late": 3, "on_leave": 5, "total": 30},
     *   "code": 200
     * }
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year'  => ['required', 'integer', 'min:2000'],
        ]);

        $summary = $this->attendanceRepo->summaryForDelegate(
            $request->user()->id,
            $request->input('month'),
            (int) $request->input('year')
        );

        return $this->successResponse($summary, 'تم جلب ملخص الحضور بنجاح');
    }

    /**
     * Show Attendance Record
     *
     * Returns details of a single attendance record belonging to the delegate.
     *
     * @group HR - Attendance
     *
     * @urlParam attendance integer required The attendance record ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل سجل الحضور بنجاح",
     *   "data": {"id": 1, "date": "2026-04-26", "check_in": "08:00:00", "check_out": "17:00:00", "status": "present"},
     *   "code": 200
     * }
     */
    public function show(Request $request, int $attendanceId): JsonResponse
    {
        $record = $this->attendanceRepo->getById($attendanceId);

        if ($record->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('غير مصرح');
        }

        return $this->successResponse($this->formatAttendance($record), 'تم جلب تفاصيل سجل الحضور بنجاح');
    }

    private function formatAttendance($record): array
    {
        return [
            'id'           => $record->id,
            'date'         => $record->date,
            'check_in'     => $record->check_in,
            'check_out'    => $record->check_out,
            'status'       => $record->status,
            'status_label' => $record->status_label,
            'notes'        => $record->notes,
            'created_at'   => $record->created_at?->toISOString(),
        ];
    }
}
