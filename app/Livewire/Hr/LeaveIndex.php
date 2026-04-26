<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrLeave;
use App\Services\HrLeaveService;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public ?int $filterDelegate = null;

    public bool $showRejectModal = false;
    public ?int $rejectId = null;
    public string $rejectionReason = '';

    protected $queryString = ['search', 'filterStatus', 'filterType', 'filterDelegate'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterDelegate(): void { $this->resetPage(); }

    public function approve(int $id, HrLeaveService $service): void
    {
        $service->approve($id);
        session()->flash('success', 'تمت الموافقة على الإجازة');
    }

    public function openReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(HrLeaveService $service): void
    {
        $this->validate(['rejectionReason' => 'required|string|max:500'], [
            'rejectionReason.required' => 'سبب الرفض مطلوب',
        ]);
        $service->reject($this->rejectId, $this->rejectionReason);
        $this->showRejectModal = false;
        session()->flash('success', 'تم رفض الإجازة');
    }

    public function delete(int $id, HrLeaveService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'تم الحذف بنجاح');
    }

    public function render(HrLeaveService $service)
    {
        $leaves = $service->all([
            'delegate_id' => $this->filterDelegate,
            'status'      => $this->filterStatus,
            'type'        => $this->filterType,
        ]);

        if ($this->search) {
            $leaves = HrLeave::with(['delegate', 'approvedBy'])
                ->whereHas('delegate', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('livewire.hr.leave-index', [
            'leaves'    => $leaves,
            'delegates' => Delegate::orderBy('name')->get(['id', 'name']),
            'types'     => HrLeave::typeLabels(),
            'statuses'  => HrLeave::statusLabels(),
        ]);
    }
}
