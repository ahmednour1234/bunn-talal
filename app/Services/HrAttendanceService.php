<?php

namespace App\Services;

use App\Models\HrAttendance;
use Illuminate\Support\Facades\Auth;

class HrAttendanceService
{
    public function all(array $filters = [])
    {
        $q = HrAttendance::with('delegate')
            ->orderByDesc('date');

        if (!empty($filters['delegate_id'])) {
            $q->where('delegate_id', $filters['delegate_id']);
        }

        if (!empty($filters['date_from'])) {
            $q->where('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $q->where('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->paginate(20);
    }

    public function getById(int $id): HrAttendance
    {
        return HrAttendance::with('delegate')->findOrFail($id);
    }

    public function create(array $data): HrAttendance
    {
        $data['admin_id'] = Auth::guard('admin')->id();
        return HrAttendance::create($data);
    }

    public function update(int $id, array $data): HrAttendance
    {
        $attendance = HrAttendance::findOrFail($id);
        $attendance->update($data);
        return $attendance;
    }

    public function delete(int $id): void
    {
        HrAttendance::findOrFail($id)->delete();
    }
}
