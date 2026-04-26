<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrAttendance extends Model
{
    protected $fillable = [
        'delegate_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function statusLabels(): array
    {
        return [
            'present'  => 'حاضر',
            'absent'   => 'غائب',
            'late'     => 'متأخر',
            'on_leave' => 'إجازة',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
