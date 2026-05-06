<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UnitResource;
use App\Models\Unit;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    use ApiResponse;

    /**
     * List Measurement Units
     *
     * Returns all active measurement units. Useful when building sale order items.
     *
     * @group Reference Data
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب وحدات القياس بنجاح",
     *   "data": [{
     *     "id": 1, "name": "كيلوغرام", "symbol": "كغ",
     *     "type": "weight", "is_base_unit": true,
     *     "base_unit_id": null, "conversion_factor": 1
     *   }],
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $units = Unit::where('is_active', true)
            ->select('id', 'name', 'symbol', 'type', 'base_unit_id', 'conversion_factor')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return $this->successResponse(UnitResource::collection($units)->resolve(), 'تم جلب وحدات القياس بنجاح');
    }
}
