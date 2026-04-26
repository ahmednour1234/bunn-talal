<?php

namespace App\Livewire\Hr;

use App\Models\Delegate;
use App\Models\HrAttendance;
use App\Services\HrAttendanceService;
use Livewire\Component;

class AttendanceForm extends Component
{
    public ?int $attendanceId = null;
    public ?int $delegate_id = null;
    public string $date = '';
    public string $check_in = '';
    public string $check_out = '';
    public string $status = 'present';
    public string $notes = '';

    public function mount(HrAttendanceService $service, ?int $id = null, ?int $delegateId = null): void
    {
        $this->date = now()->format('Y-m-d');

        if ($delegateId) {
            $this->delegate_id = $delegateId;
        }

        if ($id) {
            $this->attendanceId = $id;
            $att = $service->getById($id);
            $this->delegate_id = $att->delegate_id;
            $this->date        = $att->date->format('Y-m-d');
            $this->check_in    = $att->check_in ?? '';
            $this->check_out   = $att->check_out ?? '';
            $this->status      = $att->status;
            $this->notes       = $att->notes ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'delegate_id' => 'required|exists:delegates,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'status'      => 'required|in:present,absent,late,on_leave',
            'notes'       => 'nullable|string|max:500',
        ];
    }

    protected function messages(): array
    {
        return [
            'delegate_id.required' => 'اختر المندوب',
            'date.required'        => 'التاريخ مطلوب',
            'status.required'      => 'الحالة مطلوبة',
            'check_in.date_format' => 'صيغة وقت الحضور غير صحيحة',
            'check_out.date_format' => 'صيغة وقت الانصراف غير صحيحة',
        ];
    }

    public function save(HrAttendanceService $service): void
    {
        $this->validate();

        $data = [
            'delegate_id' => $this->delegate_id,
            'date'        => $this->date,
            'check_in'    => $this->check_in ?: null,
            'check_out'   => $this->check_out ?: null,
            'status'      => $this->status,
            'notes'       => $this->notes ?: null,
        ];

        if ($this->attendanceId) {
            $service->update($this->attendanceId, $data);
            session()->flash('success', 'تم تحديث سجل الحضور بنجاح');
        } else {
            $service->create($data);
            session()->flash('success', 'تم إضافة سجل الحضور بنجاح');
        }

        $this->redirect(route('hr.attendance.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.attendance-form', [
            'delegates' => Delegate::orderBy('name')->get(['id', 'name']),
            'statuses'  => HrAttendance::statusLabels(),
        ]);
    }
}
