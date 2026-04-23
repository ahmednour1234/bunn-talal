<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponse;

    /**
     * List Accounts
     *
     * Returns accounts that are visible to delegates (for recording financial transactions).
     *
     * @group Reference Data
     *
     * @response 200 {"status": true, "message": "تم جلب الحسابات بنجاح", "data": [{"id": 1, "name": "الصندوق الرئيسي", "account_number": "101"}], "code": 200}
     */
    public function index(Request $request): JsonResponse
    {
        $accounts = Account::where('visible_to_delegate', true)
            ->where('is_active', true)
            ->select('id', 'name', 'account_number')
            ->orderBy('name')
            ->get()
            ->map(fn($a) => [
                'id'             => $a->id,
                'name'           => $a->name,
                'account_number' => $a->account_number,
            ]);

        return $this->successResponse($accounts, 'تم جلب الحسابات بنجاح');
    }
}
