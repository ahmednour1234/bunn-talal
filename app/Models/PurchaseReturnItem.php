<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'purchase_invoice_item_id',
        'product_id',
        'quantity',
        'unit_id',
        'unit_price',
        'loss_amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'loss_amount' => 'decimal:2',
        ];
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(PurchaseInvoiceItem::class, 'purchase_invoice_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
