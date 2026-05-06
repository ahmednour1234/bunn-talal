<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DelegateLoanResource;
use App\Models\DelegateLoan;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DelegateLoanController extends Controller
{
    use ApiResponse;

    /**
     * List Loans & Custody
     *
     * Returns the delegate's custody/loan records with a summary of totals.
     *
     * @group Loans & Custody
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب سجلات العهدة بنجاح",
     *   "data": {
     *     "summary": {"total_amount": 5000, "total_paid": 3000, "total_remaining": 2000, "overdue_count": 1},
     *     "loans": [{
     *       "id": 1, "amount": 5000, "paid_amount": 3000, "remaining": 2000,
     *       "due_date": "2026-05-01", "is_paid": false, "is_overdue": false,
     *       "paid_at": null, "note": "عهدة رحلة", "created_at": "2026-04-01T00:00:00Z"
     *     }]
     *   },
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $delegate = $request->user();

        $loans = DelegateLoan::where('delegate_id', $delegate->id)
            ->latest()
            ->get();

        $summary = [
            'total_amount'     => $loans->sum('amount'),
            'total_paid'       => $loans->sum('paid_amount'),
            'total_remaining'  => $loans->sum('remaining'),
            'overdue_count'    => $loans->where('is_overdue', true)->count(),
        ];

        return $this->successResponse([
            'summary' => $summary,
            'loans'   => DelegateLoanResource::collection($loans)->resolve(),
        ], 'تم جلب سجلات العهدة بنجاح');
    }
}
