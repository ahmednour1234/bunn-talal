<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'email'                 => $this->email,
            'phone'                 => $this->phone,
            'national_id'           => $this->national_id,
            'national_id_image'     => $this->national_id_image ? asset('storage/' . $this->national_id_image) : null,
            'credit_sales_limit'    => $this->credit_sales_limit,
            'cash_custody'          => $this->cash_custody,
            'total_collected'       => $this->total_collected,
            'total_due'             => $this->total_due,
            'sales_commission_rate' => $this->sales_commission_rate,
            'is_active'             => $this->is_active,
            'areas'      => $this->areas->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values(),
            'branches'   => $this->branches->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->values(),
            'categories' => $this->categories->map(fn ($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'image' => $c->image ? asset('storage/' . $c->image) : null,
            ])->values(),
        ];
    }
}
