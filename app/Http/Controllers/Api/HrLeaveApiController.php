<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\HrLeaveRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HrLeaveApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HrLeaveRepositoryInterface $leaveRepo) {}

    /**
     * List My Leaves
     *
     * Returns a paginated list of the authenticated delegate's leave records.
     *
     * @group HR - Leaves
     *
     * @queryParam status string Filter by status. One of: pending, approved, rejected. Example: approved
     * @queryParam type string Filter by type. One of: annual, sick, emergency, unpaid. Example: annual
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب سجلات الإجازات بنجاح",
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1, "type": "annual", "type_label": "إجازة سنوية",
     *         "start_date": "2026-05-01", "end_date": "2026-05-07", "days": 7,
     *         "reason": "إجازة سنوية", "status": "approved", "status_label": "موافق عليها",
     *         "approved_at": "2026-04-25T10:00:00Z", "rejection_reason": null,
     *         "created_at": "2026-04-20T08:00:00Z"
     *       }
     *     ],
     *     "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1}
     *   },
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegate = $request->user();

        $filters = $request->only(['status', 'type']);

        $leaves = $this->leaveRepo->forDelegate($delegate->id, $filters);

        return $this->successResponse([
            'data' => $leaves->getCollection()->map(fn($l) => $this->formatLeave($l)),
            'meta' => [
                'current_page' => $leaves->currentPage(),
                'last_page'    => $leaves->lastPage(),
                'per_page'     => $leaves->perPage(),
                'total'        => $leaves->total(),
            ],
        ], 'تم جلب سجلات الإجازات بنجاح');
    }

    /**
     * Show Leave
     *
     * Returns details of a single leave record belonging to the delegate.
     *
     * @group HR - Leaves
     *
     * @urlParam leave integer required The leave ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل الإجازة بنجاح",
     *   "data": {"id": 1, "type": "annual", "type_label": "إجازة سنوية", "days": 7, "status": "approved"},
     *   "code": 200
     * }
     * @response 403 scenario="Not owned" {"status": false, "message": "غير مصرح", "data": null, "code": 403}
     */
    public function show(Request $request, int $leaveId): JsonResponse
    {
        $leave = $this->leaveRepo->getById($leaveId);

        if ($leave->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('غير مصرح');
        }

        return $this->successResponse($this->formatLeave($leave), 'تم جلب تفاصيل الإجازة بنجاح');
    }

    /**
     * Request Leave
     *
     * Submit a new leave request. The leave starts in `pending` status until approved by an admin.
     *
     * @group HR - Leaves
     *
     * @bodyParam type string required Leave type. One of: annual, sick, emergency, unpaid. Example: annual
     * @bodyParam start_date string required Start date (YYYY-MM-DD). Example: 2026-05-01
     * @bodyParam end_date string required End date (YYYY-MM-DD), must be >= start_date. Example: 2026-05-07
     * @bodyParam reason string nullable Reason for the leave. Example: إجازة سنوية مستحقة
     *
     * @response 201 scenario="Created" {
     *   "status": true, "message": "تم تقديم طلب الإجازة بنجاح",
     *   "data": {"id": 5, "type": "annual", "start_date": "2026-05-01", "end_date": "2026-05-07", "days": 7, "status": "pending"},
     *   "code": 201
     * }
     * @response 422 scenario="Validation error" {"status": false, "message": "خطأ في البيانات المدخلة", "data": null, "code": 422, "errors": {}}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'       => ['required', Rule::in(['annual', 'sick', 'emergency', 'unpaid'])],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'gte:start_date'],
            'reason'     => ['nullable', 'string', 'max:500'],
        ]);

        $validated['delegate_id'] = $request->user()->id;
        $validated['status']      = 'pending';

        $leave = $this->leaveRepo->create($validated);

        return $this->successResponse($this->formatLeave($leave), 'تم تقديم طلب الإجازة بنجاح', 201);
    }

    private function formatLeave($leave): array
    {
        return [
            'id'               => $leave->id,
            'type'             => $leave->type,
            'type_label'       => $leave->type_label,
            'start_date'       => $leave->start_date?->toDateString(),
            'end_date'         => $leave->end_date?->toDateString(),
            'days'             => $leave->days,
            'reason'           => $leave->reason,
            'status'           => $leave->status,
            'status_label'     => $leave->status_label,
            'approved_at'      => $leave->approved_at?->toISOString(),
            'rejection_reason' => $leave->rejection_reason,
            'created_at'       => $leave->created_at?->toISOString(),
        ];
    }
}
