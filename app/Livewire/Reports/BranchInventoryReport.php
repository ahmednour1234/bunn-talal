<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Services\BranchReportService;
use Livewire\Component;

class BranchInventoryReport extends Component
{
    public string $branchFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $activeTab = 'summary';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['summary', 'details'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['branchFilter', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $service = app(BranchReportService::class);

        $branchId = $this->branchFilter ? (int) $this->branchFilter : null;
        $inventory = $service->getBranchInventoryReport($branchId, $this->dateFrom ?: null, $this->dateTo ?: null);
        $summary = $service->getAllBranchesSummary($this->dateFrom ?: null, $this->dateTo ?: null);

        return view('livewire.reports.branch-inventory-report', [
            'inventory' => $inventory,
            'summary' => $summary,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
