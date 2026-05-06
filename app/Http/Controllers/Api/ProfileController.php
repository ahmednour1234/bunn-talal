<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProfileResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get Profile
     *
     * Returns the authenticated delegate's profile, assigned areas, branches and categories.
     *
     * @group Profile
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب بيانات الحساب بنجاح",
     *   "data": {
     *     "id": 1, "name": "أحمد", "email": "ahmed@example.com", "phone": "0501234567",
     *     "credit_sales_limit": 5000, "cash_custody": 1000, "total_collected": 3200,
     *     "total_due": 1800, "sales_commission_rate": 2.5, "is_active": true,
     *     "areas": [{"id": 1, "name": "صنعاء"}],
     *     "branches": [{"id": 1, "name": "الفرع الرئيسي"}],
     *     "categories": [{"id": 1, "name": "أجهزة", "image": null}]
     *   },
     *   "code": 200
     * }
     */
    public function show(Request $request): JsonResponse
    {
        $delegate = $request->user()->load(['areas:id,name', 'branches:id,name', 'categories:id,name,image']);

        return $this->successResponse(ProfileResource::make($delegate)->resolve(), 'تم جلب بيانات الحساب بنجاح');
    }
}
