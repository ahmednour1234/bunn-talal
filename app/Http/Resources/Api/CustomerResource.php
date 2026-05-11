<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        $openingBalance = (float) $this->opening_balance;
        $totalInvoiced  = (float) ($this->total_invoiced ?? 0);
        $totalPaid      = (float) ($this->total_paid ?? 0);
        $netBalance     = $openingBalance + $totalInvoiced - $totalPaid;

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
            'opening_balance'      => $openingBalance,
            'total_invoiced'       => $totalInvoiced,
            'total_paid'           => $totalPaid,
            'net_balance'          => $netBalance,
            'balance'              => (float) $this->balance,
            'debt'                 => (float) $this->balance,
            'area'                 => $this->area
                ? ['id' => $this->area->id, 'name' => $this->area->name]
                : null,
        ];
    }
}
