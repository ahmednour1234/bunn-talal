<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrLeave;
use App\Services\HrLeaveService;
use Livewire\Component;

class LeaveForm extends Component
{
    public ?int $leaveId = null;
    public ?int $delegate_id = null;
    public string $type = 'annual';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';
    public string $status = 'pending';

    public function mount(HrLeaveService $service, ?int $id = null, ?int $delegateId = null): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');

        if ($delegateId) {
            $this->delegate_id = $delegateId;
        }

        if ($id) {
            $this->leaveId = $id;
            $leave = $service->getById($id);
            $this->delegate_id = $leave->delegate_id;
            $this->type        = $leave->type;
            $this->start_date  = $leave->start_date->format('Y-m-d');
            $this->end_date    = $leave->end_date->format('Y-m-d');
            $this->reason      = $leave->reason ?? '';
            $this->status      = $leave->status;
        }
    }

    protected function rules(): array
    {
        return [
            'delegate_id' => 'required|exists:delegates,id',
            'type'        => 'required|in:annual,sick,emergency,unpaid',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string|max:1000',
            'status'      => 'required|in:pending,approved,rejected',
        ];
    }

    protected function messages(): array
    {
        return [
            'delegate_id.required' => 'اختر المندوب',
            'type.required'        => 'اختر نوع الإجازة',
            'start_date.required'  => 'تاريخ البداية مطلوب',
            'end_date.required'    => 'تاريخ النهاية مطلوب',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
        ];
    }

    public function save(HrLeaveService $service): void
    {
        $this->validate();

        $data = [
            'delegate_id' => $this->delegate_id,
            'type'        => $this->type,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'reason'      => $this->reason ?: null,
            'status'      => $this->status,
        ];

        if ($this->leaveId) {
            $service->update($this->leaveId, $data);
            session()->flash('success', 'تم تحديث الإجازة بنجاح');
        } else {
            $service->create($data);
            session()->flash('success', 'تم إضافة الإجازة بنجاح');
        }

        $this->redirect(route('hr.leaves.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.leave-form', [
            'delegates' => Delegate::orderBy('name')->get(['id', 'name']),
            'types'     => HrLeave::typeLabels(),
            'statuses'  => HrLeave::statusLabels(),
        ]);
    }
}
