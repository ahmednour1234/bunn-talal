<?php

namespace App\Livewire\Hr;

use App\Models\Account;
use App\Models\Delegate;
use App\Models\HrSalary;
use App\Models\Treasury;
use App\Services\HrSalaryService;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryIndex extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public ?int $filterDelegate = null;
    public string $filterYear = '';
    public string $filterMonth = '';

    protected $queryString = ['filterStatus', 'filterDelegate', 'filterYear', 'filterMonth'];

    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterDelegate(): void { $this->resetPage(); }
    public function updatingFilterYear(): void { $this->resetPage(); }
    public function updatingFilterMonth(): void { $this->resetPage(); }

    public function pay(int $id, HrSalaryService $service): void
    {
        try {
            $service->pay($id);
            session()->flash('success', 'تم صرف الراتب وتسجيله في الحسابات');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function delete(int $id, HrSalaryService $service): void
    {
        try {
            $service->delete($id);
            session()->flash('success', 'تم الحذف بنجاح');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(HrSalaryService $service)
    {
        $salaries = $service->all([
            'delegate_id' => $this->filterDelegate,
            'status'      => $this->filterStatus,
            'year'        => $this->filterYear,
            'month'       => $this->filterMonth,
        ]);

        return view('livewire.hr.salary-index', [
            'salaries'  => $salaries,
            'delegates' => Delegate::orderBy('name')->get(['id', 'name']),
            'months'    => HrSalary::monthLabels(),
            'years'     => range(now()->year, now()->year - 3),
        ]);
    }
}
