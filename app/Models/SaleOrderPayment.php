<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleOrderPayment extends Model
{
    protected $fillable = [
        'sale_order_id',
        'treasury_id',
        'admin_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount'       => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(SaleOrder::class, 'sale_order_id');
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
