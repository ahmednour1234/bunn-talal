<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'collection_number' => $this->collection_number,
            'status'            => $this->status,
            'status_label'      => $this->status_label,
            'collection_date'   => $this->collection_date,
            'total_amount'      => $this->total_amount,
            'notes'             => $this->notes,
            'trip_id'           => $this->trip_id,
            'customer'          => $this->customer
                ? ['id' => $this->customer->id, 'name' => $this->customer->name, 'phone' => $this->customer->phone]
                : null,
            'items'             => CollectionItemResource::collection($this->items)->resolve(),
        ];
    }
}
