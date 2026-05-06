<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class HrSalaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'month'        => $this->month,
            'month_label'  => $this->month_label,
            'year'         => $this->year,
            'basic_salary' => $this->basic_salary,
            'commissions'  => $this->commissions,
            'bonuses'      => $this->bonuses,
            'deductions'   => $this->deductions,
            'net_salary'   => $this->net_salary,
            'status'       => $this->status,
            'paid_at'      => $this->paid_at?->toDateString(),
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
