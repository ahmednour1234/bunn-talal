<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'trip_id'          => $this->trip_id,
            'trip'             => $this->whenLoaded('trip', fn () =>
                $this->trip ? [
                    'id'          => $this->trip->id,
                    'trip_number' => $this->trip->trip_number,
                    'status'      => $this->trip->status,
                ] : null
            ),
            'customer_name'    => $this->customer_name,
            'customer_phone'   => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'notes'            => $this->notes,
            'status'           => $this->status,
            'status_label'     => $this->statusLabel(),
            'created_at'       => $this->created_at,
            'items'            => BookingRequestItemResource::collection($this->items)->resolve(),
            'converted_order'  => $this->whenLoaded('convertedOrder', fn () =>
                $this->convertedOrder ? [
                    'id'           => $this->convertedOrder->id,
                    'order_number' => $this->convertedOrder->order_number,
                    'status'       => $this->convertedOrder->status,
                    'total'        => $this->convertedOrder->total,
                ] : null
            ),
        ];
    }
}
