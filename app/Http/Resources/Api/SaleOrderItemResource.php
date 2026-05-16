<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleOrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $grossAmount    = round((float)$this->quantity * (float)$this->unit_price, 2);
        $itemDiscount   = round($grossAmount + (float)$this->tax_amount - (float)$this->total, 2);

        return [
            'id'            => $this->id,
            'product'       => $this->product
                ? ['id' => $this->product->id, 'name' => $this->product->name]
                : null,
            'unit'          => $this->unit
                ? ['id' => $this->unit->id, 'name' => $this->unit->name]
                : null,
            'quantity'      => $this->quantity,
            'unit_price'    => $this->unit_price,
            'gross_amount'  => $grossAmount,
            'discount'      => $this->discount,
            'discount_type' => $this->discount_type,
            'item_discount' => $itemDiscount,
            'tax_amount'    => $this->tax_amount,
            'total'         => $this->total,
        ];
    }
}
