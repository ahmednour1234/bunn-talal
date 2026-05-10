<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHrLeaveRequest;
use App\Http\Resources\Api\HrLeaveResource;
use App\Services\HrLeaveService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrLeaveApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HrLeaveService $hrLeaveService) {}

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
        $filters  = $request->only(['status', 'type']);
        $leaves   = $this->hrLeaveService->forDelegate($delegate->id, $filters);

        return $this->successResponse([
            'data' => HrLeaveResource::collection($leaves->getCollection())->resolve(),
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
        try {
            $leave = $this->hrLeaveService->getById($leaveId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('الإجازة غير موجودة');
        }

        if ($leave->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('غير مصرح');
        }

        return $this->successResponse(HrLeaveResource::make($leave)->resolve(), 'تم جلب تفاصيل الإجازة بنجاح');
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
    public function store(StoreHrLeaveRequest $request): JsonResponse
    {
        $data                = $request->validated();
        $data['delegate_id'] = $request->user()->id;
        $data['status']      = 'pending';

        $leave = $this->hrLeaveService->createLeave($data);

        return $this->successResponse(HrLeaveResource::make($leave)->resolve(), 'تم تقديم طلب الإجازة بنجاح', 201);
    }
}
