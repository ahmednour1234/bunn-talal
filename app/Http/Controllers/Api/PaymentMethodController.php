<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    /**
     * List Payment Methods
     *
     * Returns the available payment method keys and their Arabic labels.
     *
     * @group Reference Data
     *
     * @response 200 {
     *   "status": true,
     *   "message": "تم جلب طرق الدفع بنجاح",
     *   "data": [
     *     {"key": "cash", "label": "نقداً"},
     *     {"key": "credit", "label": "آجل"},
     *     {"key": "partial", "label": "جزئي (دفعة مقدمة)"}
     *   ],
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $methods = [
            ['key' => 'cash',    'label' => 'نقداً'],
            ['key' => 'credit',  'label' => 'آجل'],
            ['key' => 'partial', 'label' => 'جزئي (دفعة مقدمة)'],
        ];

        return $this->successResponse($methods, 'تم جلب طرق الدفع بنجاح');
    }
}
