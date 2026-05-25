<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_number',
        'customer_id',
        'treasury_id',
        'admin_id',
        'amount',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'   => 'date',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CustomerPayment $p) {
            if (empty($p->payment_number)) {
                $p->payment_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('payment_number', 'like', "CPAY-{$today}-%")
            ->orderByDesc('payment_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->payment_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "CPAY-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
