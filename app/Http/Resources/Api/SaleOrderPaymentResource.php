<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleOrderPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'amount'         => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_date'   => $this->payment_date,
            'notes'          => $this->notes,
        ];
    }
}
