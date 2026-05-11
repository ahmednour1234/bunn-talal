<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'phone'                => $this->phone,
            'email'                => $this->email,
            'address'              => $this->address,
            'latitude'             => $this->latitude,
            'longitude'            => $this->longitude,
            'classification'       => $this->classification,
            'classification_label' => $this->classification_label,
            'credit_limit'         => (float) $this->credit_limit,
            'opening_balance'      => (float) $this->opening_balance,
            'balance'              => (float) $this->balance,
            'debt'                 => (float) $this->balance,   // مديونية العميل
            'area'                 => $this->area
                ? ['id' => $this->area->id, 'name' => $this->area->name]
                : null,
        ];
    }
}
