<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class HrLeaveResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'type_label'       => $this->type_label,
            'start_date'       => $this->start_date?->toDateString(),
            'end_date'         => $this->end_date?->toDateString(),
            'days'             => $this->days,
            'reason'           => $this->reason,
            'status'           => $this->status,
            'status_label'     => $this->status_label,
            'approved_at'      => $this->approved_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
