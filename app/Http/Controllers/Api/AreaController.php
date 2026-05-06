<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AreaResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    use ApiResponse;

    /**
     * List Areas
     *
     * Returns active regions (areas) assigned to the authenticated delegate.
     *
     * @group Reference Data
     *
     * @response 200 {"status": true, "message": "تم جلب المناطق بنجاح", "data": [{"id": 1, "name": "صنعاء"}], "code": 200}
     */
    public function index(Request $request): JsonResponse
    {
        $areas = $request->user()
            ->areas()
            ->where('is_active', true)
            ->select('areas.id', 'areas.name')
            ->get();

        return $this->successResponse(AreaResource::collection($areas)->resolve(), 'تم جلب المناطق بنجاح');
    }
}
