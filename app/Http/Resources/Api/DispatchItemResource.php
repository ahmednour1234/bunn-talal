<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class DispatchItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'product'           => $this->product
                ? ['id' => $this->product->id, 'name' => $this->product->name]
                : null,
            'quantity'          => $this->quantity,
            'returned_quantity' => $this->returned_quantity,
            'sold_quantity'     => $this->sold_quantity,
            'cost_price'        => $this->cost_price,
            'selling_price'     => $this->selling_price,
        ];
    }
}
