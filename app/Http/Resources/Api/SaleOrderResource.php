<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'order_number'         => $this->order_number,
            'status'               => $this->status,
            'status_label'         => $this->status_label,
            'payment_method'       => $this->payment_method,
            'payment_method_label' => $this->payment_method_label,
            'date'                 => $this->date,
            'due_date'             => $this->due_date,
            'subtotal'             => $this->subtotal,
            'discount_amount'      => $this->discount_amount,
            'discount_type'        => $this->discount_type,
            'tax_amount'           => $this->tax_amount,
            'total'                => $this->total,
            'paid_amount'          => $this->paid_amount,
            'remaining_amount'     => $this->remaining_amount,
            'customer'             => $this->customer
                ? ['id' => $this->customer->id, 'name' => $this->customer->name, 'phone' => $this->customer->phone, 'debt' => (float) $this->customer->balance]
                : null,
            'notes'                => $this->notes,
            'trip_id'              => $this->trip_id,
            'items'                => $this->whenLoaded('items', fn () =>
                SaleOrderItemResource::collection($this->items)->resolve()
            ),
            'payments'             => $this->whenLoaded('payments', fn () =>
                SaleOrderPaymentResource::collection($this->payments)->resolve()
            ),
        ];
    }
}
