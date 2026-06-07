<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrSalary;
use Livewire\Component;

class SalariesReport extends Component
{
    public string $delegateFilter = '';
    public string $statusFilter   = '';
    public string $yearFilter     = '';
    public string $monthFilter    = '';

    public function mount(): void
    {
        $this->yearFilter = (string) now()->year;
    }

    public function clearFilters(): void
    {
        $this->reset(['delegateFilter', 'statusFilter', 'monthFilter']);
        $this->yearFilter = (string) now()->year;
    }

    public function render()
    {
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();

        $q = HrSalary::with('delegate')
            ->when($scopedBranchId, fn($query) => $query->whereHas('delegate.branches', fn($b) => $b->where('branches.id', $scopedBranchId)))
            ->orderByDesc('year')
            ->orderByDesc('month');

        if ($this->delegateFilter) {
            $q->where('delegate_id', $this->delegateFilter);
        }

        if ($this->statusFilter) {
            $q->where('status', $this->statusFilter);
        }

        if ($this->yearFilter) {
            $q->where('year', $this->yearFilter);
        }

        if ($this->monthFilter) {
            $q->where('month', $this->monthFilter);
        }

        $salaries = $q->get();

        $summary = [
            'total_records'  => $salaries->count(),
            'paid_count'     => $salaries->where('status', 'paid')->count(),
            'pending_count'  => $salaries->where('status', 'pending')->count(),
            'total_basic'    => $salaries->sum('basic_salary'),
            'total_commissions' => $salaries->sum('commissions'),
            'total_bonuses'  => $salaries->sum('bonuses'),
            'total_deductions' => $salaries->sum('deductions'),
            'total_net'      => $salaries->sum(fn($s) => $s->net_salary),
            'total_paid_net' => $salaries->where('status', 'paid')->sum(fn($s) => $s->net_salary),
        ];

        return view('livewire.hr.salaries-report', [
            'salaries'  => $salaries,
            'summary'   => $summary,
            'delegates' => Delegate::where('is_active', true)
                ->when($scopedBranchId, fn($query) => $query->whereHas('branches', fn($b) => $b->where('branches.id', $scopedBranchId)))
                ->orderBy('name')->get(),
            'months'    => HrSalary::monthLabels(),
            'years'     => range(now()->year, now()->year - 4),
        ]);
    }
}
