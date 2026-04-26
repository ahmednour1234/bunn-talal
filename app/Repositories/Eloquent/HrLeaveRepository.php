<?php

namespace App\Repositories\Eloquent;

use App\Models\HrLeave;
use App\Repositories\Contracts\HrLeaveRepositoryInterface;

class HrLeaveRepository extends BaseRepository implements HrLeaveRepositoryInterface
{
    public function __construct(HrLeave $model)
    {
        parent::__construct($model);
    }

    public function forDelegate(int $delegateId, array $filters = [])
    {
        $q = $this->model->newQuery()
            ->where('delegate_id', $delegateId)
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        return $q->paginate(15);
    }

    public function approve(int $id, int $adminId): mixed
    {
        $leave = $this->getById($id);
        $leave->update([
            'status'      => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
        return $leave;
    }

    public function reject(int $id, string $reason): mixed
    {
        $leave = $this->getById($id);
        $leave->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);
        return $leave;
    }
}
