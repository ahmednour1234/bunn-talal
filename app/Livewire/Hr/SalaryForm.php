<?php

namespace App\Livewire\Hr;

use App\Models\Account;
use App\Models\Delegate;
use App\Models\HrSalary;
use App\Models\Treasury;
use App\Services\HrSalaryService;
use Livewire\Component;

class SalaryForm extends Component
{
    public ?int $salaryId = null;
    public ?int $delegate_id = null;
    public string $month = '';
    public string $year = '';
    public string $basic_salary = '0';
    public string $commissions = '0';
    public string $bonuses = '0';
    public string $deductions = '0';
    public ?int $account_id = null;
    public ?int $treasury_id = null;
    public string $notes = '';
    public string $status = 'pending';

    // Delegate financial info card (reactive)
    public array $delegateInfo = [];

    public function mount(HrSalaryService $service, ?int $id = null, ?int $delegateId = null): void
    {
        $this->month = now()->month;
        $this->year  = now()->year;

        if ($delegateId) {
            $this->delegate_id = $delegateId;
            $this->loadDelegateInfo();
        }

        if ($id) {
            $this->salaryId   = $id;
            $sal = $service->getById($id);
            $this->delegate_id  = $sal->delegate_id;
            $this->month        = $sal->month;
            $this->year         = $sal->year;
            $this->basic_salary = (string) ($sal->basic_salary * 1);
            $this->commissions  = (string) ($sal->commissions * 1);
            $this->bonuses      = (string) ($sal->bonuses * 1);
            $this->deductions   = (string) ($sal->deductions * 1);
            $this->account_id   = $sal->account_id;
            $this->treasury_id  = $sal->treasury_id;
            $this->notes        = $sal->notes ?? '';
            $this->status       = $sal->status;
            $this->loadDelegateInfo();
        }
    }

    public function updatedDelegateId(): void
    {
        $this->loadDelegateInfo();
    }

    private function loadDelegateInfo(): void
    {
        if (!$this->delegate_id) {
            $this->delegateInfo = [];
            return;
        }

        $d = Delegate::find($this->delegate_id, [
            'id', 'name', 'sales_commission_rate', 'cash_custody', 'total_collected', 'total_due',
        ]);

        if (!$d) {
            $this->delegateInfo = [];
            return;
        }

        $this->delegateInfo = [
            'name'                 => $d->name,
            'sales_commission_rate'=> (float) $d->sales_commission_rate,
            'cash_custody'         => (float) $d->cash_custody,
            'total_collected'      => (float) $d->total_collected,
            'total_due'            => (float) $d->total_due,
        ];
    }

    public function applyDueAsDeduction(): void
    {
        if (!empty($this->delegateInfo['total_due'])) {
            $this->deductions = (string) $this->delegateInfo['total_due'];
        }
    }

    protected function rules(): array
    {
        return [
            'delegate_id'  => 'required|exists:delegates,id',
            'month'        => 'required|integer|min:1|max:12',
            'year'         => 'required|integer|min:2020|max:2100',
            'basic_salary' => 'required|numeric|min:0',
            'commissions'  => 'required|numeric|min:0',
            'bonuses'      => 'required|numeric|min:0',
            'deductions'   => 'required|numeric|min:0',
            'account_id'   => 'nullable|exists:accounts,id',
            'treasury_id'  => 'nullable|exists:treasuries,id',
            'notes'        => 'nullable|string|max:500',
            'status'       => 'required|in:pending,paid',
        ];
    }

    protected function messages(): array
    {
        return [
            'delegate_id.required'  => 'اختر المندوب',
            'month.required'        => 'الشهر مطلوب',
            'year.required'         => 'السنة مطلوبة',
            'basic_salary.required' => 'الراتب الأساسي مطلوب',
        ];
    }

    public function getNetSalaryProperty(): float
    {
        return max(0, floatval($this->basic_salary) + floatval($this->commissions) + floatval($this->bonuses) - floatval($this->deductions));
    }

    public function save(HrSalaryService $service): void
    {
        $this->validate();

        $data = [
            'delegate_id'  => $this->delegate_id,
            'month'        => $this->month,
            'year'         => $this->year,
            'basic_salary' => $this->basic_salary,
            'commissions'  => $this->commissions,
            'bonuses'      => $this->bonuses,
            'deductions'   => $this->deductions,
            'account_id'   => $this->account_id,
            'treasury_id'  => $this->treasury_id,
            'notes'        => $this->notes ?: null,
            'status'       => $this->status,
        ];

        if ($this->salaryId) {
            $service->update($this->salaryId, $data);
            session()->flash('success', 'تم تحديث الراتب بنجاح');
        } else {
            $service->create($data);
            session()->flash('success', 'تم إضافة الراتب بنجاح');
        }

        $this->redirect(route('hr.salaries.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.salary-form', [
            'delegates'  => Delegate::orderBy('name')->get(['id', 'name']),
            'accounts'   => Account::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'months'     => HrSalary::monthLabels(),
            'years'      => range(now()->year, now()->year - 3),
        ]);
    }
}
