<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class HrAttendanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'date'         => $this->date,
            'check_in'     => $this->check_in,
            'check_out'    => $this->check_out,
            'status'       => $this->status,
            'status_label' => $this->status_label,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
