<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DispatchResource;
use App\Models\InventoryDispatch;
use App\Models\Trip;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    use ApiResponse;

    /**
     * List Dispatches
     *
     * Returns inventory dispatches for a trip (view-only — delegates cannot create dispatches).
     *
     * @group Inventory Dispatches
     *
     * @urlParam trip integer required The trip ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب أوامر الصرف بنجاح",
     *   "data": [{"id": 1, "status": "approved", "total_cost": 5000}],
     *   "code": 200
     * }
     */
    public function index(Request $request, $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $dispatches = InventoryDispatch::where('trip_id', $tripId)
            ->where('delegate_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->successResponse(DispatchResource::collection($dispatches)->resolve(), 'تم جلب أوامر الصرف بنجاح');
    }

    /**
     * Get Dispatch
     *
     * Returns detailed information about a single inventory dispatch including all items.
     *
     * @group Inventory Dispatches
     *
     * @urlParam dispatch integer required The dispatch ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل أمر الصرف بنجاح",
     *   "data": {"id": 1, "status": "approved", "items": []},
     *   "code": 200
     * }
     */
    public function show(Request $request, int $dispatchId): JsonResponse
    {
        $dispatch = InventoryDispatch::with([
            'items.product:id,name,image',
            'branch:id,name',
        ])->findOrFail($dispatchId);

        if ($dispatch->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذا أمر الصرف لا يخصك');
        }

        return $this->successResponse(DispatchResource::make($dispatch)->resolve(), 'تم جلب تفاصيل أمر الصرف بنجاح');
    }
}
