<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'status_label'   => $this->status_label,
            'date'           => $this->date,
            'total_cost'     => $this->total_cost,
            'expected_sales' => $this->expected_sales,
            'actual_sales'   => $this->actual_sales,
            'notes'          => $this->notes,
            'trip_id'        => $this->trip_id,
            'trip'           => $this->whenLoaded('trip', fn () =>
                $this->trip ? ['id' => $this->trip->id, 'trip_number' => $this->trip->trip_number, 'status' => $this->trip->status] : null
            ),
            'branch'         => $this->whenLoaded('branch', fn () =>
                $this->branch ? ['id' => $this->branch->id, 'name' => $this->branch->name] : null
            ),
            'items'          => $this->whenLoaded('items', fn () =>
                DispatchItemResource::collection($this->items)->resolve()
            ),
        ];
    }
}
