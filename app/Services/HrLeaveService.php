<?php

namespace App\Services;

use App\Models\HrLeave;
use Illuminate\Support\Facades\Auth;

class HrLeaveService
{
    public function all(array $filters = [])
    {
        $q = HrLeave::with(['delegate', 'approvedBy'])
            ->orderByDesc('created_at');

        if (!empty($filters['delegate_id'])) {
            $q->where('delegate_id', $filters['delegate_id']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        return $q->paginate(15);
    }

    public function getById(int $id): HrLeave
    {
        return HrLeave::with(['delegate', 'approvedBy'])->findOrFail($id);
    }

    public function create(array $data): HrLeave
    {
        $data['admin_id'] = Auth::guard('admin')->id();
        return HrLeave::create($data);
    }

    public function update(int $id, array $data): HrLeave
    {
        $leave = HrLeave::findOrFail($id);
        $leave->update($data);
        return $leave;
    }

    public function approve(int $id): HrLeave
    {
        $leave = HrLeave::findOrFail($id);
        $leave->update([
            'status'      => 'approved',
            'approved_by' => Auth::guard('admin')->id(),
            'approved_at' => now(),
        ]);
        return $leave;
    }

    public function reject(int $id, string $reason): HrLeave
    {
        $leave = HrLeave::findOrFail($id);
        $leave->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);
        return $leave;
    }

    public function delete(int $id): void
    {
        HrLeave::findOrFail($id)->delete();
    }
}
