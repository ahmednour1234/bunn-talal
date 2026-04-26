<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrLeave;
use Livewire\Component;

class LeavesReport extends Component
{
    public string $delegateFilter = '';
    public string $typeFilter     = '';
    public string $statusFilter   = '';
    public string $dateFrom       = '';
    public string $dateTo         = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function clearFilters(): void
    {
        $this->reset(['delegateFilter', 'typeFilter', 'statusFilter']);
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $q = HrLeave::with('delegate')
            ->orderByDesc('start_date');

        if ($this->delegateFilter) {
            $q->where('delegate_id', $this->delegateFilter);
        }

        if ($this->typeFilter) {
            $q->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $q->where('status', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $q->where('start_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $q->where('start_date', '<=', $this->dateTo);
        }

        $leaves = $q->get();

        // Summary stats
        $summary = [
            'total'    => $leaves->count(),
            'approved' => $leaves->where('status', 'approved')->count(),
            'pending'  => $leaves->where('status', 'pending')->count(),
            'rejected' => $leaves->where('status', 'rejected')->count(),
            'total_days' => $leaves->where('status', 'approved')->sum(fn($l) => $l->days),
        ];

        // By type breakdown
        $byType = $leaves->groupBy('type')->map->count();

        return view('livewire.hr.leaves-report', [
            'leaves'    => $leaves,
            'summary'   => $summary,
            'byType'    => $byType,
            'delegates' => Delegate::where('is_active', true)->orderBy('name')->get(),
            'types'     => HrLeave::typeLabels(),
            'statuses'  => HrLeave::statusLabels(),
        ]);
    }
}
