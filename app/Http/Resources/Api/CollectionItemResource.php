<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CollectionItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'amount'     => $this->amount,
            'notes'      => $this->notes,
            'sale_order' => $this->saleOrder ? [
                'id'           => $this->saleOrder->id,
                'order_number' => $this->saleOrder->order_number,
                'total'        => $this->saleOrder->total,
                'paid_amount'  => $this->saleOrder->paid_amount,
            ] : null,
        ];
    }
}
