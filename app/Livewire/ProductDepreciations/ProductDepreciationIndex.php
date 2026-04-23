<?php

namespace App\Livewire\ProductDepreciations;

use App\Models\Branch;
use App\Models\ProductDepreciation;
use App\Services\ProductDepreciationService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductDepreciationIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $branchFilter = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingBranchFilter() { $this->resetPage(); }

    public function render(ProductDepreciationService $service)
    {
        return view('livewire.product-depreciations.product-depreciation-index', [
            'depreciations' => $service->paginateWithFilters(
                10,
                $this->search ?: null,
                $this->statusFilter ?: null,
                $this->branchFilter ? (int) $this->branchFilter : null
            ),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => ProductDepreciation::statusLabels(),
        ]);
    }
}
