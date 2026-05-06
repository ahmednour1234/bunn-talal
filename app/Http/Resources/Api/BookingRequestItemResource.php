<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingRequestItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'product'    => $this->product
                ? ['id' => $this->product->id, 'name' => $this->product->name]
                : null,
            'unit'       => $this->unit
                ? ['id' => $this->unit->id, 'name' => $this->unit->name]
                : null,
            'quantity'   => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal'   => $this->subtotal,
            'notes'      => $this->notes,
        ];
    }
}
