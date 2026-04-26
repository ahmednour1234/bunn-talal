<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrAttendance;
use App\Services\HrAttendanceService;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';
    public ?int $filterDelegate = null;

    protected $queryString = ['search', 'filterStatus', 'filterDateFrom', 'filterDateTo', 'filterDelegate'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterDateFrom(): void { $this->resetPage(); }
    public function updatingFilterDateTo(): void { $this->resetPage(); }
    public function updatingFilterDelegate(): void { $this->resetPage(); }

    public function delete(int $id, HrAttendanceService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'تم الحذف بنجاح');
    }

    public function render(HrAttendanceService $service)
    {
        $attendances = HrAttendance::with('delegate')
            ->when($this->filterDelegate, fn($q) => $q->where('delegate_id', $this->filterDelegate))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDateFrom, fn($q) => $q->where('date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->where('date', '<=', $this->filterDateTo))
            ->when($this->search, fn($q) => $q->whereHas('delegate', fn($d) => $d->where('name', 'like', '%' . $this->search . '%')))
            ->orderByDesc('date')
            ->paginate(20);

        return view('livewire.hr.attendance-index', [
            'attendances' => $attendances,
            'delegates'   => Delegate::orderBy('name')->get(['id', 'name']),
            'statuses'    => HrAttendance::statusLabels(),
        ]);
    }
}
