<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoicePayment extends Model
{
    protected $fillable = [
        'purchase_invoice_id',
        'amount',
        'payment_date',
        'treasury_id',
        'payment_method',
        'admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
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
