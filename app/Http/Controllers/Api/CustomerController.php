<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    /**
     * List Customers
     *
     * Returns active customers in the delegate's assigned areas, including their outstanding balance.
     *
     * @group Reference Data
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب العملاء بنجاح",
     *   "data": [{
     *     "id": 1, "name": "عميل 1", "phone": "0501111111",
     *     "balance": 500, "classification": "vip",
     *     "area": {"id": 1, "name": "صنعاء"}
     *   }],
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegate = $request->user();

        $areaIds = $delegate->areas()->pluck('areas.id');

        $customers = Customer::whereIn('area_id', $areaIds)
            ->where('is_active', true)
            ->with('area:id,name')
            ->select('id', 'name', 'phone', 'email', 'area_id', 'address', 'latitude', 'longitude', 'classification', 'balance')
            ->get()
            ->map(function ($customer) {
                return [
                    'id'             => $customer->id,
                    'name'           => $customer->name,
                    'phone'          => $customer->phone,
                    'email'          => $customer->email,
                    'address'        => $customer->address,
                    'latitude'       => $customer->latitude,
                    'longitude'      => $customer->longitude,
                    'classification' => $customer->classification,
                    'classification_label' => $customer->classification_label,
                    'balance'        => $customer->balance,
                    'area'           => $customer->area ? ['id' => $customer->area->id, 'name' => $customer->area->name] : null,
                ];
            });

        return $this->successResponse($customers, 'تم جلب العملاء بنجاح');
    }
}
