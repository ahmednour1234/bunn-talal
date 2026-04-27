<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use App\Models\Trip;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    use ApiResponse;

    /**
     * Delegate Statistics
     *
     * Returns key statistics for the authenticated delegate.
     *
     * @group Statistics
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب الإحصائيات بنجاح",
     *   "data": [
     *     { "label": "إجمالي المبيعات", "value": 15000.00 },
     *     { "label": "إجمالي التحصيلات", "value": 12000.00 },
     *     { "label": "إجمالي المرتجعات", "value": 500.00 },
     *     { "label": "المبلغ المستحق", "value": 3000.00 },
     *     { "label": "عدد الرحلات", "value": 10 },
     *     { "label": "عدد أوامر البيع", "value": 45 },
     *     { "label": "عدد التحصيلات", "value": 38 },
     *     { "label": "عدد المرتجعات", "value": 5 }
     *   ],
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegateId = $request->user()->id;

        $totalSales = SaleOrder::where('delegate_id', $delegateId)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $totalCollections = Collection::where('delegate_id', $delegateId)
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalReturns = SaleReturn::where('delegate_id', $delegateId)
            ->whereNotIn('status', ['cancelled'])
            ->sum('refund_amount');

        $totalDue = $request->user()->total_due;

        $tripsCount = Trip::where('delegate_id', $delegateId)->count();

        $ordersCount = SaleOrder::where('delegate_id', $delegateId)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $collectionsCount = Collection::where('delegate_id', $delegateId)
            ->where('status', 'completed')
            ->count();

        $returnsCount = SaleReturn::where('delegate_id', $delegateId)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $statistics = [
            ['label' => 'إجمالي المبيعات',   'value' => (float) $totalSales],
            ['label' => 'إجمالي التحصيلات',  'value' => (float) $totalCollections],
            ['label' => 'إجمالي المرتجعات',  'value' => (float) $totalReturns],
            ['label' => 'المبلغ المستحق',     'value' => (float) $totalDue],
            ['label' => 'عدد الرحلات',        'value' => $tripsCount],
            ['label' => 'عدد أوامر البيع',    'value' => $ordersCount],
            ['label' => 'عدد التحصيلات',      'value' => $collectionsCount],
            ['label' => 'عدد المرتجعات',      'value' => $returnsCount],
        ];

        return $this->successResponse($statistics, 'تم جلب الإحصائيات بنجاح');
    }
}
