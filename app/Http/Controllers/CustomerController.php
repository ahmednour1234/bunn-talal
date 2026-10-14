<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use App\Exports\CustomersExport;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService)
    {
    }

    public function exportExcel(Request $request)
    {
        $branchFilter = (string) $request->input('branch_id', '');
        $selectedIds = collect(explode(',', (string) $request->input('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // قيّد التصدير بفرع المستخدم إن كان مرتبطاً بفرع
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();
        if ($scopedBranchId) {
            $branchFilter = (string) $scopedBranchId;
        }

        return Excel::download(
            new CustomersExport(
                (string) $request->input('search', ''),
                (string) $request->input('classification', ''),
                (string) $request->input('area_id', ''),
                $branchFilter,
                $selectedIds,
                (string) $request->input('delegate_id', ''),
                $request->boolean('debt_only'),
            ),
            'العملاء.xlsx'
        );
    }

    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $classification = $request->input('classification', '');
        $areaId         = $request->input('area_id', '');
        $delegateId     = $request->input('delegate_id', '');
        $debtOnly       = $request->boolean('debt_only');

        $query = Customer::query()->with(['area', 'delegates:id,name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($classification) {
            $query->where('classification', $classification);
        }

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        if ($delegateId) {
            $query->whereHas('delegates', fn ($q) => $q->where('delegates.id', $delegateId));
        }

        $totalDebt = null;
        if ($debtOnly) {
            // المديونية محسوبة في PHP، فنحسبها لكل العملاء المطابقين ثم نفلتر بالـ id
            $allDebts  = \App\Support\CustomerDebt::forCustomers((clone $query)->get(['customers.id']));
            $debtorIds = array_keys(array_filter($allDebts, fn ($v) => $v > 0.009));
            $totalDebt = array_sum(array_intersect_key($allDebts, array_flip($debtorIds)));
            $query->whereIn('customers.id', $debtorIds);
        }

        $customers = $query->latest()->paginate(10)->withQueryString();
        $areas     = Area::where('is_active', true)->orderBy('name')->get();
        $delegates = \App\Models\Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $debts     = \App\Support\CustomerDebt::forCustomers($customers->getCollection());

        return view('pages.customers.index', compact('customers', 'areas', 'delegates', 'search', 'classification', 'areaId', 'delegateId', 'debtOnly', 'totalDebt', 'debts'));
    }

    public function toggleActive(int $id)
    {
        $admin = auth('admin')->user();
        if (! $admin->hasPermission('customers.edit')) {
            return back()->with('error', 'ليس لديك صلاحية التعديل');
        }
        $customer = $this->customerService->toggleActive($id);
        return back()->with('success', $customer->is_active ? 'تم تفعيل العميل' : 'تم تعطيل العميل');
    }

    public function destroy(int $id)
    {
        $admin = auth('admin')->user();
        if (! $admin->hasPermission('customers.delete')) {
            return back()->with('error', 'ليس لديك صلاحية الحذف');
        }
        try {
            $this->customerService->deleteCustomer($id);
            return back()->with('success', 'تم حذف العميل بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'لا يمكن حذف هذا العميل لوجود سجلات مرتبطة به');
        }
    }

    public function create()
    {
        return view('pages.customers.create');
    }

    public function edit(int $id)
    {
        return view('pages.customers.edit', compact('id'));
    }

    public function show(int $id)
    {
        return view('pages.customers.show', compact('id'));
    }

    public function trash()
    {
        return view('pages.customers.trash');
    }
}
