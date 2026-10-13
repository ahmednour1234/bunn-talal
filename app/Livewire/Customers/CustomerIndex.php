<?php

namespace App\Livewire\Customers;

use App\Models\Branch;
use App\Models\Customer;
use App\Services\CustomerService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $classificationFilter = '';
    public string $areaFilter = '';
    public string $branchFilter = '';
    public string $delegateFilter = '';
    public bool $debtOnly = false;
    public array $selectedCustomers = [];

    protected CustomerService $customerService;

    public function boot(CustomerService $customerService): void
    {
        $this->customerService = $customerService;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClassificationFilter()
    {
        $this->resetPage();
    }

    public function updatingAreaFilter()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function updatingDelegateFilter()
    {
        $this->resetPage();
    }

    public function updatingDebtOnly()
    {
        $this->resetPage();
    }

    public function updatedSelectedCustomers(): void
    {
        $this->selectedCustomers = array_values(array_unique(array_filter(array_map('intval', $this->selectedCustomers))));
    }

    public function toggleSelectPage(): void
    {
        $pageIds = $this->getCurrentPageCustomerIds();
        $selected = array_map('intval', $this->selectedCustomers);

        if (empty(array_diff($pageIds, $selected))) {
            $this->selectedCustomers = array_values(array_diff($selected, $pageIds));
            return;
        }

        $this->selectedCustomers = array_values(array_unique(array_merge($selected, $pageIds)));
    }

    public function getExportUrlProperty(): string
    {
        return route('customers.export.excel', [
            'search' => $this->search,
            'classification' => $this->classificationFilter,
            'area_id' => $this->areaFilter,
            'branch_id' => $this->branchFilter,
            'ids' => implode(',', $this->selectedCustomers),
        ]);
    }

    private function getCurrentPageCustomerIds(): array
    {
        return $this->buildCustomerQuery()
            ->latest()
            ->paginate(10)
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function buildCustomerQuery()
    {
        $query = Customer::query()->with(['area', 'branch', 'delegates:id,name']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->classificationFilter) {
            $query->where('classification', $this->classificationFilter);
        }

        if ($this->areaFilter) {
            $query->where('area_id', $this->areaFilter);
        }

        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        if ($this->delegateFilter) {
            $delegateId = $this->delegateFilter;
            $query->whereHas('delegates', fn ($q) => $q->where('delegates.id', $delegateId));
        }

        return $query;
    }

    public function toggleActive(int $id)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('customers.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $customer = $this->customerService->toggleActive($id);
        session()->flash('success', $customer->is_active ? 'تم تفعيل العميل' : 'تم تعطيل العميل');
    }

    public function delete(int $id)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('customers.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        try {
            $this->customerService->deleteCustomer($id);
            session()->flash('success', 'تم حذف العميل بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'لا يمكن حذف هذا العميل لوجود سجلات مرتبطة به');
        }
    }

    public function render()
    {
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();
        if ($scopedBranchId) {
            $this->branchFilter = (string) $scopedBranchId;
        }

        $query = $this->buildCustomerQuery();

        $totalDebt = null;
        if ($this->debtOnly) {
            // المديونية محسوبة في PHP، فنحسبها لكل العملاء المطابقين ثم نفلتر بالـ id
            $allDebts = \App\Support\CustomerDebt::forCustomers((clone $query)->get(['customers.id']));
            $debtorIds = array_keys(array_filter($allDebts, fn ($v) => $v > 0.009));
            $totalDebt = array_sum(array_intersect_key($allDebts, array_flip($debtorIds)));
            $query->whereIn('customers.id', $debtorIds);
        }

        $customers = $query->latest()->paginate(10);

        return view('livewire.customers.customer-index', [
            'customers' => $customers,
            'debts' => \App\Support\CustomerDebt::forCustomers($customers->getCollection()),
            'classificationLabels' => Customer::classificationLabels(),
            'areas' => \App\Models\Area::where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'delegates' => \App\Models\Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'totalDebt' => $totalDebt,
            'scopedBranchId' => $scopedBranchId,
        ]);
    }
}
