<?php

namespace App\Models;

use App\Traits\HasRoles;
use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasRoles, HasPermissions, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'branch_id',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function typeLabels(): array
    {
        return [
            'super'            => 'مدير عام',
            'branches_manager' => 'مدير فروع',
            'branch_manager'   => 'مدير فرع',
            'statistics_only'  => 'مدير إحصائيات فرعي',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return static::typeLabels()[$this->type] ?? $this->type;
    }

    /**
     * The branch id this admin is restricted to when viewing data/stats/reports.
     * Returns null for admins who may see all branches (super / branches_manager),
     * otherwise the admin's own branch_id.
     */
    public function scopedBranchId(): ?int
    {
        return in_array($this->type, ['super', 'branches_manager']) ? null : $this->branch_id;
    }

    /**
     * Whether this admin is locked to a single branch (cannot pick other branches).
     */
    public function isBranchScoped(): bool
    {
        return $this->scopedBranchId() !== null;
    }
}
