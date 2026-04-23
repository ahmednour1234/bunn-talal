<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function index(Request $request, int $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('هذه الرحلة لا تخصك');
        }

        $dispatches = InventoryDispatch::where('trip_id', $tripId)
            ->where('delegate_id', $request->user()->id)
            ->with(['items.product:id,name,image'])
            ->latest()
            ->get()
            ->map(fn($d) => $this->formatDispatch($d));

        return $this->successResponse($dispatches, 'تم جلب أوامر الصرف بنجاح');
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

        return $this->successResponse($this->formatDispatch($dispatch, detailed: true), 'تم جلب تفاصيل أمر الصرف بنجاح');
    }

    private function formatDispatch(InventoryDispatch $d, bool $detailed = false): array
    {
        $data = [
            'id'             => $d->id,
            'status'         => $d->status,
            'status_label'   => $d->status_label,
            'date'           => $d->date,
            'total_cost'     => $d->total_cost,
            'expected_sales' => $d->expected_sales,
            'actual_sales'   => $d->actual_sales,
            'notes'          => $d->notes,
            'trip_id'        => $d->trip_id,
        ];

        if ($detailed) {
            $data['branch'] = $d->branch ? ['id' => $d->branch->id, 'name' => $d->branch->name] : null;
            $data['items']  = $d->items->map(fn($item) => [
                'id'                => $item->id,
                'product'           => $item->product ? ['id' => $item->product->id, 'name' => $item->product->name] : null,
                'quantity'          => $item->quantity,
                'returned_quantity' => $item->returned_quantity,
                'sold_quantity'     => $item->sold_quantity,
                'cost_price'        => $item->cost_price,
                'selling_price'     => $item->selling_price,
            ])->values();
        }

        return $data;
    }
}
