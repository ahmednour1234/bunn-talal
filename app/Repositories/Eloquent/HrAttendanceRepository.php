<?php

namespace App\Repositories\Eloquent;

use App\Models\HrAttendance;
use App\Repositories\Contracts\HrAttendanceRepositoryInterface;

class HrAttendanceRepository extends BaseRepository implements HrAttendanceRepositoryInterface
{
    public function __construct(HrAttendance $model)
    {
        parent::__construct($model);
    }

    public function forDelegate(int $delegateId, array $filters = [])
    {
        $q = $this->model->newQuery()
            ->where('delegate_id', $delegateId)
            ->orderByDesc('date');

        if (!empty($filters['date_from'])) {
            $q->where('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $q->where('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->paginate(30);
    }

    public function summaryForDelegate(int $delegateId, string $month, int $year): array
    {
        $from = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $to   = date('Y-m-t', strtotime($from));

        $rows = $this->model->newQuery()
            ->where('delegate_id', $delegateId)
            ->whereBetween('date', [$from, $to])
            ->get();

        return [
            'present'  => $rows->where('status', 'present')->count(),
            'absent'   => $rows->where('status', 'absent')->count(),
            'late'     => $rows->where('status', 'late')->count(),
            'on_leave' => $rows->where('status', 'on_leave')->count(),
            'total'    => $rows->count(),
        ];
    }
}
