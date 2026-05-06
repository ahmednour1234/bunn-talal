<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'return_number' => $this->return_number,
            'status'        => $this->status,
            'status_label'  => $this->status_label,
            'date'          => $this->date,
            'subtotal'      => $this->subtotal,
            'refund_amount' => $this->refund_amount,
            'customer'      => $this->customer
                ? ['id' => $this->customer->id, 'name' => $this->customer->name]
                : null,
            'notes'         => $this->notes,
            'trip_id'       => $this->trip_id,
            'order'         => $this->whenLoaded('order', fn () =>
                $this->order
                    ? ['id' => $this->order->id, 'order_number' => $this->order->order_number]
                    : null
            ),
            'items'         => $this->whenLoaded('items', fn () =>
                SaleReturnItemResource::collection($this->items)->resolve()
            ),
        ];
    }
}
