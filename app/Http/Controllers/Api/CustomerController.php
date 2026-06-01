<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerResource;
use App\Models\Area;
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
     * Returns all active customers with optional search filters.
     *
     * @group Reference Data
     *
     * @queryParam search string Filter by customer name or phone number. Example: أحمد
     * @queryParam area string Filter by area/city name (partial match). Example: صنعاء
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم جلب العملاء بنجاح",
     *   "data": [{
     *     "id": 1, "name": "عميل 1", "phone": "0501111111",
     *     "balance": 500, "classification": "regular",
     *     "area": {"id": 1, "name": "صنعاء"}
     *   }],
     *   "code": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'area'   => ['nullable', 'string', 'max:100'],
        ]);

        $delegate = $request->user();

        $query = Customer::where('is_active', true)
            ->with('area:id,name')
            ->whereHas('delegates', fn ($q) => $q->where('delegates.id', $delegate->id));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('area')) {
            $query->whereHas('area', fn ($q) =>
                $q->where('name', 'like', '%' . $request->area . '%')
            );
        }

        $customers = $query
            ->select('id', 'name', 'phone', 'email', 'area_id', 'address', 'latitude', 'longitude', 'classification', 'balance', 'opening_balance', 'credit_limit')
            // آجل وجزئي فقط، بدون مسودات أو ملغيات
            ->withSum(['saleOrders as total_invoiced' => fn ($q) => $q
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->whereIn('payment_method', ['credit', 'partial'])
            ], 'total')
            // ما دُفع عند إنشاء الفاتورة (الدفع الجزئي المبدئي)
            ->withSum(['saleOrders as total_order_paid' => fn ($q) => $q
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->whereIn('payment_method', ['credit', 'partial'])
            ], 'paid_amount')
            // مرتجعات مؤكدة أو مكتملة فقط
            ->withSum(['saleReturns as total_returned' => fn ($q) => $q
                ->whereIn('status', ['confirmed', 'refunded'])
            ], 'refund_amount')
            // تحصيلات غير ملغية فقط
            ->withSum(['collections as total_paid' => fn ($q) => $q
                ->whereNotIn('status', ['cancelled'])
            ], 'total_amount')
            // دفعات للعميل (مرتجعات نقدية)
            ->withSum('customerPayments as total_customer_payments', 'amount')
            ->get();

        return $this->successResponse(CustomerResource::collection($customers)->resolve(), 'تم جلب العملاء بنجاح');
    }

    /**
     * Add Customer (Delegate)
     *
     * Allows the authenticated delegate to register a new customer.
     * The customer is created as **inactive** until an admin approves and activates the account.
     *
     * @group Customers
     *
     * @bodyParam name     string  required Customer full name. Example: محمد علي
     * @bodyParam phone    string  nullable Customer phone number. Example: 0501234567
     * @bodyParam email    string  nullable Customer email address. Example: customer@example.com
     * @bodyParam area_id  integer nullable Area ID the customer belongs to. Example: 2
     * @bodyParam address  string  nullable Customer address. Example: شارع الجمهورية
     * @bodyParam latitude  number nullable GPS latitude. Example: 15.3529
     * @bodyParam longitude number nullable GPS longitude. Example: 44.2068
     *
     * @response 201 scenario="Created" {
     *   "status": true,
     *   "message": "تم إضافة العميل بنجاح وهو بانتظار تفعيل الإدارة",
     *   "data": {"id": 10, "name": "محمد علي", "phone": "0501234567", "is_active": false},
     *   "code": 201
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'email'      => ['nullable', 'email', 'max:100'],
            'area_id'    => ['nullable', 'integer', 'exists:areas,id'],
            'address'    => ['nullable', 'string', 'max:255'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $customer = Customer::create([
            ...$validated,
            'is_active'       => false,
            'credit_limit'    => 0,
            'opening_balance' => 0,
            'balance'         => 0,
            'classification'  => 'regular',
        ]);

        // Auto-attach the newly created customer to the authenticated delegate
        $customer->delegates()->attach($request->user()->id);

        return $this->successResponse(
            CustomerResource::make($customer)->resolve(),
            'تم إضافة العميل بنجاح وهو بانتظار تفعيل الإدارة',
            201
        );
    }
}
