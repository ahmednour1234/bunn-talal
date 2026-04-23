<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDepreciation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'depreciation_number',
        'branch_id',
        'admin_id',
        'approved_by',
        'approved_at',
        'date',
        'status',
        'reason',
        'total_loss',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
            'total_loss' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductDepreciation $dep) {
            if (empty($dep->depreciation_number)) {
                $dep->depreciation_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('depreciation_number', 'like', "DEP-{$today}-%")
            ->orderByDesc('depreciation_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->depreciation_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "DEP-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'في انتظار الموافقة',
            'approved' => 'تمت الموافقة',
            'rejected' => 'مرفوض',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function approvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(ProductDepreciationItem::class);
    }
}
