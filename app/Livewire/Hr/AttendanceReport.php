<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrAttendance;
use Livewire\Component;

class AttendanceReport extends Component
{
    public string $delegateFilter = '';
    public string $statusFilter   = '';
    public string $dateFrom       = '';
    public string $dateTo         = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function clearFilters(): void
    {
        $this->reset(['delegateFilter', 'statusFilter']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $q = HrAttendance::with('delegate')
            ->orderByDesc('date');

        if ($this->delegateFilter) {
            $q->where('delegate_id', $this->delegateFilter);
        }

        if ($this->statusFilter) {
            $q->where('status', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $q->where('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $q->where('date', '<=', $this->dateTo);
        }

        $records = $q->get();

        $summary = [
            'total'    => $records->count(),
            'present'  => $records->where('status', 'present')->count(),
            'absent'   => $records->where('status', 'absent')->count(),
            'late'     => $records->where('status', 'late')->count(),
            'on_leave' => $records->where('status', 'on_leave')->count(),
        ];

        // Per-delegate attendance summary
        $perDelegate = $records->groupBy('delegate_id')->map(function ($rows) {
            $delegate = $rows->first()->delegate;
            return [
                'name'     => $delegate?->name ?? '-',
                'present'  => $rows->where('status', 'present')->count(),
                'absent'   => $rows->where('status', 'absent')->count(),
                'late'     => $rows->where('status', 'late')->count(),
                'on_leave' => $rows->where('status', 'on_leave')->count(),
                'total'    => $rows->count(),
            ];
        })->values();

        return view('livewire.hr.attendance-report', [
            'records'     => $records,
            'summary'     => $summary,
            'perDelegate' => $perDelegate,
            'delegates'   => Delegate::where('is_active', true)->orderBy('name')->get(),
            'statuses'    => HrAttendance::statusLabels(),
        ]);
    }
}
