<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'trip_number'            => $this->trip_number,
            'status'                 => $this->status,
            'status_label'           => $this->statusLabel(),
            'start_date'             => $this->start_date,
            'expected_return_date'   => $this->expected_return_date,
            'actual_return_date'     => $this->actual_return_date,
            'notes'                  => $this->notes,
            'branch'                 => $this->branch
                ? ['id' => $this->branch->id, 'name' => $this->branch->name]
                : null,
            'total_dispatched_value' => $this->total_dispatched_value,
            'total_invoiced'         => $this->total_invoiced,
            'total_collected'        => $this->total_collected,
            'total_returned_value'   => $this->total_returned_value,
            'outstanding'            => $this->outstanding,
            'cash_custody_amount'    => $this->cash_custody_amount,
            'cash_custody_note'      => $this->cash_custody_note,
            'settlement'             => [
                'status'          => $this->settlement_status,
                'cash_expected'   => $this->settlement_cash_expected,
                'cash_actual'     => $this->settlement_cash_actual,
                'cash_deficit'    => $this->settlement_cash_deficit,
                'product_deficit' => $this->settlement_product_deficit,
                'notes'           => $this->settlement_notes,
            ],
        ];
    }
}
