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
     *   "message": "تم جلب إحصائيات الموارد البشرية بنجاح",
     *   "data": [
     *     { "label": "أيام الحضور", "value": 20 },
     *     { "label": "أيام الغياب", "value": 2 },
     *     { "label": "أيام التأخير", "value": 3 },
     *     { "label": "طلبات الإجازة", "value": 4 },
     *     { "label": "الإجازات الموافق عليها", "value": 2 },
     *     { "label": "الإجازات المعلقة", "value": 1 },
     *     { "label": "الإجازات المرفوضة", "value": 1 },
     *     { "label": "إجمالي الرواتب المدفوعة", "value": 9000.00 },
     *     { "label": "إجمالي العمولات", "value": 1500.00 },
     *     { "label": "إجمالي البدلات", "value": 500.00 },
     *     { "label": "إجمالي الخصومات", "value": 200.00 }
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


class StatisticsController extends Controller
{
    use ApiResponse;

    /**
     * Apply date filters to a query.
     *
     * Supported query params:
     *   date       YYYY-MM-DD  → exact day
     *   month      1-12        → filter by month (combined with year if provided)
     *   year       YYYY        → filter by year
     *   from_date  YYYY-MM-DD  → range start (inclusive)
     *   to_date    YYYY-MM-DD  → range end (inclusive)
     */
    private function applyDateFilters(Builder $query, Request $request, string $column): Builder
    {
        if ($request->filled('date')) {
            $query->whereDate($column, $request->date);
        }

        if ($request->filled('year')) {
            $query->whereYear($column, $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth($column, $request->month);
        }

        if ($request->filled('from_date')) {
            $query->whereDate($column, '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate($column, '<=', $request->to_date);
        }

        return $query;
    }

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

        $delegateId = $request->user()->id;

        $totalSales = $this->applyDateFilters(
            SaleOrder::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $request, 'date'
        )->sum('total');

        $totalCollections = $this->applyDateFilters(
            Collection::where('delegate_id', $delegateId)->where('status', 'completed'),
            $request, 'collection_date'
        )->sum('total_amount');

        $totalReturns = $this->applyDateFilters(
            SaleReturn::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $request, 'date'
        )->sum('refund_amount');

        $totalDue = $request->user()->total_due;

        $tripsCount = $this->applyDateFilters(
            Trip::where('delegate_id', $delegateId),
            $request, 'start_date'
        )->count();

        $ordersCount = $this->applyDateFilters(
            SaleOrder::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $request, 'date'
        )->count();

        $collectionsCount = $this->applyDateFilters(
            Collection::where('delegate_id', $delegateId)->where('status', 'completed'),
            $request, 'collection_date'
        )->count();

        $returnsCount = $this->applyDateFilters(
            SaleReturn::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $request, 'date'
        )->count();

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

    /**
     * Delegate HR Statistics
     *
     * Returns HR-related statistics for the authenticated delegate.
     * All filters are optional and can be combined freely.
     * Note: salary filters use the salary's month/year columns directly.
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
     *   "message": "تم جلب إحصائيات الموارد البشرية بنجاح",
     *   "data": [
     *     { "label": "أيام الحضور", "value": 20 },
     *     { "label": "أيام الغياب", "value": 2 },
     *     { "label": "أيام التأخير", "value": 3 },
     *     { "label": "طلبات الإجازة", "value": 4 },
     *     { "label": "الإجازات الموافق عليها", "value": 2 },
     *     { "label": "الإجازات المعلقة", "value": 1 },
     *     { "label": "الإجازات المرفوضة", "value": 1 },
     *     { "label": "إجمالي الرواتب المدفوعة", "value": 9000.00 },
     *     { "label": "إجمالي العمولات", "value": 1500.00 },
     *     { "label": "إجمالي البدلات", "value": 500.00 },
     *     { "label": "إجمالي الخصومات", "value": 200.00 }
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

        $delegateId = $request->user()->id;

        // ── Attendance ────────────────────────────────────────────────────
        $presentDays = $this->applyDateFilters(
            HrAttendance::where('delegate_id', $delegateId)->where('status', 'present'),
            $request, 'date'
        )->count();

        $absentDays = $this->applyDateFilters(
            HrAttendance::where('delegate_id', $delegateId)->where('status', 'absent'),
            $request, 'date'
        )->count();

        $lateDays = $this->applyDateFilters(
            HrAttendance::where('delegate_id', $delegateId)->where('status', 'late'),
            $request, 'date'
        )->count();

        // ── Leaves ────────────────────────────────────────────────────────
        $totalLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId),
            $request, 'start_date'
        )->count();

        $approvedLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId)->where('status', 'approved'),
            $request, 'start_date'
        )->count();

        $pendingLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId)->where('status', 'pending'),
            $request, 'start_date'
        )->count();

        $rejectedLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId)->where('status', 'rejected'),
            $request, 'start_date'
        )->count();

        // ── Salaries — month/year are dedicated columns ───────────────────
        $salaryQuery = HrSalary::where('delegate_id', $delegateId)->where('status', 'paid');

        if ($request->filled('month')) {
            $salaryQuery->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $salaryQuery->where('year', $request->year);
        }

        $totalSalaries    = (clone $salaryQuery)->sum('basic_salary');
        $totalCommissions = (clone $salaryQuery)->sum('commissions');
        $totalBonuses     = (clone $salaryQuery)->sum('bonuses');
        $totalDeductions  = (clone $salaryQuery)->sum('deductions');

        $statistics = [
            ['label' => 'أيام الحضور',               'value' => $presentDays],
            ['label' => 'أيام الغياب',                'value' => $absentDays],
            ['label' => 'أيام التأخير',               'value' => $lateDays],
            ['label' => 'طلبات الإجازة',              'value' => $totalLeaves],
            ['label' => 'الإجازات الموافق عليها',     'value' => $approvedLeaves],
            ['label' => 'الإجازات المعلقة',           'value' => $pendingLeaves],
            ['label' => 'الإجازات المرفوضة',          'value' => $rejectedLeaves],
            ['label' => 'إجمالي الرواتب المدفوعة',   'value' => (float) $totalSalaries],
            ['label' => 'إجمالي العمولات',            'value' => (float) $totalCommissions],
            ['label' => 'إجمالي البدلات',             'value' => (float) $totalBonuses],
            ['label' => 'إجمالي الخصومات',            'value' => (float) $totalDeductions],
        ];

        return $this->successResponse($statistics, 'تم جلب إحصائيات الموارد البشرية بنجاح');
    }
}


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

    /**
     * Delegate HR Statistics
     *
     * Returns HR-related statistics for the authenticated delegate.
     *
     * @group Statistics
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب إحصائيات الموارد البشرية بنجاح",
     *   "data": [
     *     { "label": "أيام الحضور", "value": 20 },
     *     { "label": "أيام الغياب", "value": 2 },
     *     { "label": "أيام التأخير", "value": 3 },
     *     { "label": "طلبات الإجازة", "value": 4 },
     *     { "label": "الإجازات الموافق عليها", "value": 2 },
     *     { "label": "الإجازات المعلقة", "value": 1 },
     *     { "label": "الإجازات المرفوضة", "value": 1 },
     *     { "label": "إجمالي الرواتب المدفوعة", "value": 9000.00 },
     *     { "label": "إجمالي العمولات", "value": 1500.00 },
     *     { "label": "إجمالي البدلات", "value": 500.00 },
     *     { "label": "إجمالي الخصومات", "value": 200.00 }
     *   ],
     *   "code": 200
     * }
     */
    public function hrStatistics(Request $request): JsonResponse
    {
        $delegateId = $request->user()->id;

        // Attendance
        $presentDays = HrAttendance::where('delegate_id', $delegateId)
            ->where('status', 'present')
            ->count();

        $absentDays = HrAttendance::where('delegate_id', $delegateId)
            ->where('status', 'absent')
            ->count();

        $lateDays = HrAttendance::where('delegate_id', $delegateId)
            ->where('status', 'late')
            ->count();

        // Leaves
        $totalLeaves    = HrLeave::where('delegate_id', $delegateId)->count();
        $approvedLeaves = HrLeave::where('delegate_id', $delegateId)->where('status', 'approved')->count();
        $pendingLeaves  = HrLeave::where('delegate_id', $delegateId)->where('status', 'pending')->count();
        $rejectedLeaves = HrLeave::where('delegate_id', $delegateId)->where('status', 'rejected')->count();

        // Salaries
        $totalSalaries   = HrSalary::where('delegate_id', $delegateId)->where('status', 'paid')->sum('basic_salary');
        $totalCommissions = HrSalary::where('delegate_id', $delegateId)->where('status', 'paid')->sum('commissions');
        $totalBonuses    = HrSalary::where('delegate_id', $delegateId)->where('status', 'paid')->sum('bonuses');
        $totalDeductions = HrSalary::where('delegate_id', $delegateId)->where('status', 'paid')->sum('deductions');

        $statistics = [
            ['label' => 'أيام الحضور',               'value' => $presentDays],
            ['label' => 'أيام الغياب',                'value' => $absentDays],
            ['label' => 'أيام التأخير',               'value' => $lateDays],
            ['label' => 'طلبات الإجازة',              'value' => $totalLeaves],
            ['label' => 'الإجازات الموافق عليها',     'value' => $approvedLeaves],
            ['label' => 'الإجازات المعلقة',           'value' => $pendingLeaves],
            ['label' => 'الإجازات المرفوضة',          'value' => $rejectedLeaves],
            ['label' => 'إجمالي الرواتب المدفوعة',   'value' => (float) $totalSalaries],
            ['label' => 'إجمالي العمولات',            'value' => (float) $totalCommissions],
            ['label' => 'إجمالي البدلات',             'value' => (float) $totalBonuses],
            ['label' => 'إجمالي الخصومات',            'value' => (float) $totalDeductions],
        ];

        return $this->successResponse($statistics, 'تم جلب إحصائيات الموارد البشرية بنجاح');
    }
}
