<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrSalary extends Model
{
    protected $fillable = [
        'delegate_id',
        'month',
        'year',
        'basic_salary',
        'commissions',
        'bonuses',
        'deductions',
        'status',
        'paid_at',
        'account_id',
        'treasury_id',
        'notes',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'commissions'  => 'decimal:2',
            'bonuses'      => 'decimal:2',
            'deductions'   => 'decimal:2',
            'paid_at'      => 'date',
        ];
    }

    public function getNetSalaryAttribute(): float
    {
        return max(0, (float)$this->basic_salary + (float)$this->commissions + (float)$this->bonuses - (float)$this->deductions);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function monthLabels(): array
    {
        return [
            1  => 'يناير',  2  => 'فبراير', 3  => 'مارس',
            4  => 'أبريل',  5  => 'مايو',   6  => 'يونيو',
            7  => 'يوليو',  8  => 'أغسطس',  9  => 'سبتمبر',
            10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
    }

    public function getMonthLabelAttribute(): string
    {
        return self::monthLabels()[$this->month] ?? $this->month;
    }
}
