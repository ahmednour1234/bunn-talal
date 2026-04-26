<?php

namespace App\Repositories\Eloquent;

use App\Models\HrSalary;
use App\Repositories\Contracts\HrSalaryRepositoryInterface;

class HrSalaryRepository extends BaseRepository implements HrSalaryRepositoryInterface
{
    public function __construct(HrSalary $model)
    {
        parent::__construct($model);
    }

    public function forDelegate(int $delegateId, array $filters = [])
    {
        $q = $this->model->newQuery()
            ->where('delegate_id', $delegateId)
            ->orderByDesc('year')
            ->orderByDesc('month');

        if (!empty($filters['year'])) {
            $q->where('year', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $q->where('month', $filters['month']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->paginate(24);
    }
}
