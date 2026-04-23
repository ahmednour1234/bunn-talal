<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'collection_number',
        'delegate_id',
        'customer_id',
        'branch_id',
        'treasury_id',
        'admin_id',
        'trip_id',
        'collection_date',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date',
            'total_amount'    => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Collection $c) {
            if (empty($c->collection_number)) {
                $c->collection_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('collection_number', 'like', "COL-{$today}-%")
            ->orderByDesc('collection_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->collection_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "COL-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function statusLabels(): array
    {
        return [
            'pending'   => 'معلق',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function items()
    {
        return $this->hasMany(CollectionItem::class);
    }
}
