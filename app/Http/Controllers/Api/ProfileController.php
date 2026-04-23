<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $data = [
            'id'                   => $delegate->id,
            'name'                 => $delegate->name,
            'email'                => $delegate->email,
            'phone'                => $delegate->phone,
            'national_id'          => $delegate->national_id,
            'national_id_image'    => $delegate->national_id_image ? asset('storage/' . $delegate->national_id_image) : null,
            'credit_sales_limit'   => $delegate->credit_sales_limit,
            'cash_custody'         => $delegate->cash_custody,
            'total_collected'      => $delegate->total_collected,
            'total_due'            => $delegate->total_due,
            'sales_commission_rate' => $delegate->sales_commission_rate,
            'is_active'            => $delegate->is_active,
            'areas'                => $delegate->areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values(),
            'branches'             => $delegate->branches->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->values(),
            'categories'           => $delegate->categories->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'image' => $c->image ? asset('storage/' . $c->image) : null,
            ])->values(),
        ];

        return $this->successResponse($data, 'تم جلب بيانات الحساب بنجاح');
    }
}
