<?php

namespace App\Livewire\Collections;

use App\Models\Branch;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Delegate;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionIndex extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $statusFilter   = '';
    public string $delegateFilter = '';
    public string $customerFilter = '';
    public string $branchFilter   = '';
    public string $dateFrom       = '';
    public string $dateTo         = '';

    public function updatingSearch()         { $this->resetPage(); }
    public function updatingStatusFilter()   { $this->resetPage(); }
    public function updatingDelegateFilter() { $this->resetPage(); }
    public function updatingCustomerFilter() { $this->resetPage(); }
    public function updatingBranchFilter()   { $this->resetPage(); }

    public function cancelCollection(int $id): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('collections.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        $col = Collection::findOrFail($id);
        if ($col->status === 'cancelled') {
            session()->flash('error', 'التحصيل ملغي بالفعل');
            return;
        }
        $col->update(['status' => 'cancelled']);
        session()->flash('success', 'تم إلغاء التحصيل');
    }

    public function render()
    {
        $query = Collection::query()
            ->with(['delegate', 'customer', 'branch', 'treasury', 'admin'])
            ->when($this->search, fn($q) => $q->where(function ($q2) {
                $q2->where('collection_number', 'like', "%{$this->search}%")
                   ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"))
                   ->orWhereHas('delegate', fn($d) => $d->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->delegateFilter, fn($q) => $q->where('delegate_id', (int) $this->delegateFilter))
            ->when($this->customerFilter, fn($q) => $q->where('customer_id', (int) $this->customerFilter))
            ->when($this->branchFilter, fn($q) => $q->where('branch_id', (int) $this->branchFilter))
            ->when($this->dateFrom, fn($q) => $q->where('collection_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('collection_date', '<=', $this->dateTo))
            ->orderByDesc('collection_date')
            ->orderByDesc('id');

        $summary = [
            'total'     => (clone $query)->sum('total_amount'),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending'   => (clone $query)->where('status', 'pending')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        return view('livewire.collections.collection-index', [
            'collections'  => $query->paginate(15),
            'delegates'    => Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'customers'    => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'branches'     => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statusLabels' => Collection::statusLabels(),
            'summary'      => $summary,
        ]);
    }
}
