<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treasury extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'branch_id',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(TreasuryTransaction::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Treasuries visible to the given scoped branch id:
     * branch-specific treasuries for that branch + shared (null-branch) treasuries.
     * Pass null to see everything.
     */
    public function scopeVisibleToBranch($query, ?int $branchId)
    {
        if ($branchId === null) {
            return $query;
        }

        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)->orWhereNull('branch_id');
        });
    }
}
