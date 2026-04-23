<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionItem extends Model
{
    protected $fillable = [
        'collection_id',
        'sale_order_id',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }
}
