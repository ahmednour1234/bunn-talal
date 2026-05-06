<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class DelegateLoanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'amount'      => $this->amount,
            'paid_amount' => $this->paid_amount,
            'remaining'   => $this->remaining,
            'due_date'    => $this->due_date,
            'is_paid'     => $this->is_paid,
            'is_overdue'  => $this->is_overdue,
            'paid_at'     => $this->paid_at,
            'note'        => $this->note,
            'created_at'  => $this->created_at,
        ];
    }
}
