<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Services\BranchReportService;
use Livewire\Component;

class BranchMovementReport extends Component
{
    public string $branchFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $report = null;

        if ($this->branchFilter) {
            $service = app(BranchReportService::class);
            $report = $service->getBranchMovementReport(
                (int) $this->branchFilter,
                $this->dateFrom ?: null,
                $this->dateTo ?: null
            );
        }

        return view('livewire.reports.branch-movement-report', [
            'report' => $report,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
