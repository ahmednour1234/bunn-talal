<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->get()
            ->map(fn($u) => [
                'id'                => $u->id,
                'name'              => $u->name,
                'symbol'            => $u->symbol,
                'type'              => $u->type,
                'type_label'        => $u->type_label,
                'is_base_unit'      => $u->isBaseUnit(),
                'base_unit_id'      => $u->base_unit_id,
                'conversion_factor' => $u->conversion_factor,
            ]);

        return $this->successResponse($units, 'تم جلب وحدات القياس بنجاح');
    }
}
