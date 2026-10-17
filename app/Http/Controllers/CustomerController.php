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

    /**
     * نفس فلاتر صفحة العملاء — تُستخدم في العرض والتصدير حتى تتطابق النتائج.
     * ترجع الاستعلام وإجمالي المديونية (عند تفعيل "عليهم مديونية فقط").
     */
    private function filteredQuery(Request $request): array
    {
        $search         = (string) $request->input('search', '');
        $classification = (string) $request->input('classification', '');
        $areaId         = (string) $request->input('area_id', '');
        $delegateId     = (string) $request->input('delegate_id', '');

        $query = Customer::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($classification !== '') {
            $query->where('classification', $classification);
        }

        if ($areaId !== '') {
            $query->where('area_id', $areaId);
        }

        if ($delegateId !== '') {
            $query->whereHas('delegates', fn ($q) => $q->where('delegates.id', $delegateId));
        }

        $totalDebt = null;
        if ($request->boolean('debt_only')) {
            // المديونية محسوبة في PHP، فنحسبها لكل العملاء المطابقين ثم نفلتر بالـ id
            $allDebts  = \App\Support\CustomerDebt::forCustomers((clone $query)->get(['customers.id']));
            $debtorIds = array_keys(array_filter($allDebts, fn ($v) => $v > 0.009));
            $totalDebt = array_sum(array_intersect_key($allDebts, array_flip($debtorIds)));
            $query->whereIn('customers.id', $debtorIds);
        }

        return [$query, $totalDebt];
    }

    public function exportExcel(Request $request)
    {
        $selectedIds = collect(explode(',', (string) $request->input('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        [$query] = $this->filteredQuery($request);

        // قيّد التصدير بفرع المستخدم إن كان مرتبطاً بفرع
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();
        if ($scopedBranchId) {
            $query->where('branch_id', $scopedBranchId);
        }

        if (! empty($selectedIds)) {
            $query->whereIn('customers.id', $selectedIds);
        }

        $ids = $query->pluck('customers.id')->all();

        return Excel::download(
            new CustomersExport(selectedIds: $ids ?: [0]),
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

        [$query, $totalDebt] = $this->filteredQuery($request);
        $query->with(['area', 'delegates:id,name']);

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
