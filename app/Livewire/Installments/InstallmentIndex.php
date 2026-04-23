<?php

namespace App\Livewire\Installments;

use App\Models\Branch;
use App\Models\InstallmentPlan;
use App\Services\InstallmentService;
use Livewire\Component;
use Livewire\WithPagination;

class InstallmentIndex extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = '';
    public string $partyFilter  = '';
    public string $branchFilter = '';
    public string $dateFrom     = '';
    public string $dateTo       = '';

    public function updatingSearch()      { $this->resetPage(); }
    public function updatingStatusFilter(){ $this->resetPage(); }
    public function updatingPartyFilter() { $this->resetPage(); }
    public function updatingBranchFilter(){ $this->resetPage(); }

    public function cancelPlan(int $id, InstallmentService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('installments.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->cancelPlan($id);
            session()->flash('success', 'تم إلغاء خطة التقسيط');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(InstallmentService $service)
    {
        $stats = $service->getSummaryStats();
        $plans = $service->paginateWithFilters(
            10,
            $this->search ?: null,
            $this->statusFilter ?: null,
            $this->partyFilter ?: null,
            $this->branchFilter ? (int) $this->branchFilter : null,
            $this->dateFrom ?: null,
            $this->dateTo ?: null,
        );

        return view('livewire.installments.installment-index', [
            'plans'        => $plans,
            'stats'        => $stats,
            'branches'     => Branch::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => InstallmentPlan::statusLabels(),
            'partyLabels'  => InstallmentPlan::partyTypeLabels(),
        ]);
    }
}
