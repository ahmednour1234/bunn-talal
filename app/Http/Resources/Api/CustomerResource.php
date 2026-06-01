<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        $openingBalance  = (float) $this->opening_balance;
        $totalInvoiced  = (float) ($this->total_invoiced ?? 0);
        $totalOrderPaid = (float) ($this->total_order_paid ?? 0);  // مدفوع عند إنشاء الفاتورة
        $totalReturned  = (float) ($this->total_returned ?? 0);    // مرتجعات مؤكدة
        $totalPaid      = (float) ($this->total_paid ?? 0);        // تحصيلات
        $totalCustomerPayments = (float) ($this->total_customer_payments ?? 0); // دفعات للعميل
        // صافي المديونية = رصيد افتتاحي + (إجمالي الفواتير الآجلة - ما دُفع منها) - المرتجعات - التحصيلات + دفعات للعميل
        $netBalance     = $openingBalance + ($totalInvoiced - $totalOrderPaid) - $totalReturned - $totalPaid + $totalCustomerPayments;

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
            'total_order_paid'     => $totalOrderPaid,
            'total_returned'       => $totalReturned,
            'total_paid'           => $totalPaid,
            'total_customer_payments' => $totalCustomerPayments,
            'net_balance'          => $netBalance,
            'balance'              => (float) $this->balance,
            'debt'                 => (float) $this->balance,
            'area'                 => $this->area
                ? ['id' => $this->area->id, 'name' => $this->area->name]
                : null,
        ];
    }
}
