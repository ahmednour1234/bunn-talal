<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleReturnItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $grossAmount    = round((float) $this->quantity * (float) $this->unit_price, 2);
        $discountAmount = round($grossAmount - (float) $this->refund_amount, 2);

        return [
            'id'              => $this->id,
            'product'         => $this->product
                ? ['id' => $this->product->id, 'name' => $this->product->name]
                : null,
            'unit'            => $this->unit
                ? ['id' => $this->unit->id, 'name' => $this->unit->name]
                : null,
            'quantity'        => $this->quantity,
            'unit_price'      => $this->unit_price,
            'gross_amount'    => $grossAmount,
            'discount_amount' => $discountAmount,
            'refund_amount'   => $this->refund_amount,
            'reason'          => $this->reason,
        ];
    }
}
