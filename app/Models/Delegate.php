<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Delegate extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'national_id',
        'national_id_image',
        'password',
        'credit_sales_limit',
        'cash_custody',
        'total_collected',
        'total_due',
        'sales_commission_rate',
        'basic_salary',
        'current_latitude',
        'current_longitude',
        'location_updated_at',
        'is_active',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'credit_sales_limit' => 'decimal:2',
            'cash_custody' => 'decimal:2',
            'total_collected' => 'decimal:2',
            'total_due' => 'decimal:2',
            'sales_commission_rate' => 'decimal:2',
            'basic_salary' => 'decimal:2',
            'current_latitude' => 'decimal:7',
            'current_longitude' => 'decimal:7',
            'location_updated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'delegate_branch');
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'delegate_area');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'delegate_category');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function bookingRequests()
    {
        return $this->hasMany(TripBookingRequest::class);
    }

    public function loans()
    {
        return $this->hasMany(DelegateLoan::class);
    }

    public function hrLeaves()
    {
        return $this->hasMany(HrLeave::class);
    }

    public function hrAttendances()
    {
        return $this->hasMany(HrAttendance::class);
    }

    public function hrSalaries()
    {
        return $this->hasMany(HrSalary::class);
    }
}
