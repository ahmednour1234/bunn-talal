<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'symbol'            => $this->symbol,
            'type'              => $this->type,
            'type_label'        => $this->type_label,
            'is_base_unit'      => $this->isBaseUnit(),
            'base_unit_id'      => $this->base_unit_id,
            'conversion_factor' => $this->conversion_factor,
        ];
    }
}
