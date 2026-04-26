<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrSalary;
use App\Repositories\Contracts\HrSalaryRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrSalaryApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HrSalaryRepositoryInterface $salaryRepo) {}

    /**
     * List My Salaries
     *
     * Returns a paginated list of the authenticated delegate's salary slips.
     *
     * @group HR - Salaries
     *
     * @queryParam year integer Filter by year. Example: 2026
     * @queryParam month integer Filter by month (1-12). Example: 4
     * @queryParam status string Filter by status. One of: pending, paid. Example: paid
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب سجلات الرواتب بنجاح",
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1, "month": 4, "month_label": "أبريل", "year": 2026,
     *         "basic_salary": "3000.00", "commissions": "500.00",
     *         "bonuses": "200.00", "deductions": "100.00", "net_salary": 3600,
     *         "status": "paid", "paid_at": "2026-04-30",
     *         "created_at": "2026-04-01T00:00:00Z"
     *       }
     *     ],
     *     "meta": {"current_page": 1, "last_page": 1, "per_page": 24, "total": 1}
     *   },
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegate = $request->user();

        $filters = $request->only(['year', 'month', 'status']);

        $salaries = $this->salaryRepo->forDelegate($delegate->id, $filters);

        return $this->successResponse([
            'data' => $salaries->getCollection()->map(fn($s) => $this->formatSalary($s)),
            'meta' => [
                'current_page' => $salaries->currentPage(),
                'last_page'    => $salaries->lastPage(),
                'per_page'     => $salaries->perPage(),
                'total'        => $salaries->total(),
            ],
        ], 'تم جلب سجلات الرواتب بنجاح');
    }

    /**
     * Show Salary Slip
     *
     * Returns details of a single salary record belonging to the delegate.
     *
     * @group HR - Salaries
     *
     * @urlParam salary integer required The salary record ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": true, "message": "تم جلب تفاصيل الراتب بنجاح",
     *   "data": {
     *     "id": 1, "month": 4, "month_label": "أبريل", "year": 2026,
     *     "basic_salary": "3000.00", "commissions": "500.00",
     *     "bonuses": "200.00", "deductions": "100.00", "net_salary": 3600,
     *     "status": "paid", "paid_at": "2026-04-30"
     *   },
     *   "code": 200
     * }
     * @response 403 scenario="Not owned" {"status": false, "message": "غير مصرح", "data": null, "code": 403}
     */
    public function show(Request $request, int $salaryId): JsonResponse
    {
        $salary = $this->salaryRepo->getById($salaryId);

        if ($salary->delegate_id !== $request->user()->id) {
            return $this->forbiddenResponse('غير مصرح');
        }

        return $this->successResponse($this->formatSalary($salary), 'تم جلب تفاصيل الراتب بنجاح');
    }

    private function formatSalary(HrSalary $salary): array
    {
        return [
            'id'            => $salary->id,
            'month'         => $salary->month,
            'month_label'   => $salary->month_label,
            'year'          => $salary->year,
            'basic_salary'  => $salary->basic_salary,
            'commissions'   => $salary->commissions,
            'bonuses'       => $salary->bonuses,
            'deductions'    => $salary->deductions,
            'net_salary'    => $salary->net_salary,
            'status'        => $salary->status,
            'paid_at'       => $salary->paid_at?->toDateString(),
            'created_at'    => $salary->created_at?->toISOString(),
        ];
    }
}
