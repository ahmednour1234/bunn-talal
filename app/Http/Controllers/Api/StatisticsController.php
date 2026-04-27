<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {}

    /**
     * Delegate Statistics
     *
     * Returns key statistics for the authenticated delegate.
     * All filters are optional and can be combined freely.
     *
     * @group Statistics
     *
     * @queryParam date string Filter by exact date (YYYY-MM-DD). Example: 2026-04-01
     * @queryParam month integer Filter by month (1-12). Example: 4
     * @queryParam year integer Filter by year. Example: 2026
     * @queryParam from_date string Filter from date (YYYY-MM-DD). Example: 2026-04-01
     * @queryParam to_date string Filter to date (YYYY-MM-DD). Example: 2026-04-30
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
        $request->validate([
            'date'      => ['nullable', 'date_format:Y-m-d'],
            'month'     => ['nullable', 'integer', 'between:1,12'],
            'year'      => ['nullable', 'integer', 'digits:4'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
        ]);

        $delegate = $request->user();
        $filters  = $request->only(['date', 'month', 'year', 'from_date', 'to_date']);

        $statistics = $this->statisticsService->getDelegateStatistics(
            $delegate->id,
            (float) $delegate->total_due,
            $filters
        );

        return $this->successResponse($statistics, 'تم جلب الإحصائيات بنجاح');
    }

    /**
     * Delegate HR Statistics
     *
     * Returns HR-related statistics for the authenticated delegate.
     * All filters are optional and can be combined freely.
     *
     * @group Statistics
     *
     * @queryParam date string Filter attendance/leaves by exact date (YYYY-MM-DD). Example: 2026-04-01
     * @queryParam month integer Filter by month (1-12). Example: 4
     * @queryParam year integer Filter by year. Example: 2026
     * @queryParam from_date string Filter from date (YYYY-MM-DD). Example: 2026-04-01
     * @queryParam to_date string Filter to date (YYYY-MM-DD). Example: 2026-04-30
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "ØªÙ… Ø¬Ù„Ø¨ Ø¥Ø­ØµØ§Ø¦ÙŠØ§Øª Ø§Ù„Ù…ÙˆØ§Ø±Ø¯ Ø§Ù„Ø¨Ø´Ø±ÙŠØ© Ø¨Ù†Ø¬Ø§Ø­",
     *   "data": [
     *     { "label": "Ø£ÙŠØ§Ù… Ø§Ù„Ø­Ø¶ÙˆØ±", "value": 20 },
     *     { "label": "Ø£ÙŠØ§Ù… Ø§Ù„ØºÙŠØ§Ø¨", "value": 2 },
     *     { "label": "Ø£ÙŠØ§Ù… Ø§Ù„ØªØ£Ø®ÙŠØ±", "value": 3 },
     *     { "label": "Ø·Ù„Ø¨Ø§Øª Ø§Ù„Ø¥Ø¬Ø§Ø²Ø©", "value": 4 },
     *     { "label": "Ø§Ù„Ø¥Ø¬Ø§Ø²Ø§Øª Ø§Ù„Ù…ÙˆØ§ÙÙ‚ Ø¹Ù„ÙŠÙ‡Ø§", "value": 2 },
     *     { "label": "Ø§Ù„Ø¥Ø¬Ø§Ø²Ø§Øª Ø§Ù„Ù…Ø¹Ù„Ù‚Ø©", "value": 1 },
     *     { "label": "Ø§Ù„Ø¥Ø¬Ø§Ø²Ø§Øª Ø§Ù„Ù…Ø±ÙÙˆØ¶Ø©", "value": 1 },
     *     { "label": "Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø±ÙˆØ§ØªØ¨ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø©", "value": 9000.00 },
     *     { "label": "Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¹Ù…ÙˆÙ„Ø§Øª", "value": 1500.00 },
     *     { "label": "Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¨Ø¯Ù„Ø§Øª", "value": 500.00 },
     *     { "label": "Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø®ØµÙˆÙ…Ø§Øª", "value": 200.00 }
     *   ],
     *   "code": 200
     * }
     */
    public function hrStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'date'      => ['nullable', 'date_format:Y-m-d'],
            'month'     => ['nullable', 'integer', 'between:1,12'],
            'year'      => ['nullable', 'integer', 'digits:4'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
        ]);

        $filters    = $request->only(['date', 'month', 'year', 'from_date', 'to_date']);
        $statistics = $this->statisticsService->getDelegateHrStatistics($request->user()->id, $filters);

        return $this->successResponse($statistics, 'تم جلب إحصائيات الموارد البشرية بنجاح');
    }
}