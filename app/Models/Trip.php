<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_number',
        'delegate_id',
        'branch_id',
        'admin_id',
        'status',
        'start_date',
        'expected_return_date',
        'actual_return_date',
        'total_dispatched_value',
        'total_invoiced',
        'total_collected',
        'total_returned_value',
        'settlement_cash_expected',
        'settlement_cash_actual',
        'settlement_cash_deficit',
        'settlement_product_deficit',
        'settlement_notes',
        'settled_by',
        'settled_at',
        'settlement_status',
        'settlement_approved_by',
        'settlement_approved_at',
        'settlement_rejection_reason',
        'notes',
        'cash_custody_amount',
        'cash_custody_treasury_id',
        'cash_custody_note',
    ];

    protected function casts(): array
    {
        return [
            'start_date'              => 'date',
            'expected_return_date'    => 'date',
            'actual_return_date'      => 'date',
            'settled_at'              => 'datetime',
            'total_dispatched_value'  => 'decimal:2',
            'total_invoiced'          => 'decimal:2',
            'total_collected'         => 'decimal:2',
            'total_returned_value'    => 'decimal:2',
            'settlement_cash_expected' => 'decimal:2',
            'settlement_cash_actual'  => 'decimal:2',
            'settlement_cash_deficit' => 'decimal:2',
            'settlement_product_deficit' => 'decimal:2',
            'cash_custody_amount'     => 'decimal:2',
            'settlement_approved_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Trip $trip) {
            if (empty($trip->trip_number)) {
                $trip->trip_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('trip_number', 'like', "TRIP-{$today}-%")
            ->orderByDesc('trip_number')
            ->value('trip_number');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;
        return "TRIP-{$today}-" . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public static function statusLabels(): array
    {
        return [
            'draft'       => 'مسودة',
            'active'      => 'نشطة',
            'in_transit'  => 'في الطريق',
            'returning'   => 'عائدة',
            'settled'     => 'مسواة',
            'cancelled'   => 'ملغية',
        ];
    }

    public static function statusColors(): array
    {
        return [
            'draft'       => 'bg-gray-100 text-gray-600',
            'active'      => 'bg-green-100 text-green-700',
            'in_transit'  => 'bg-blue-100 text-blue-700',
            'returning'   => 'bg-amber-100 text-amber-700',
            'settled'     => 'bg-primary-100 text-primary-800',
            'cancelled'   => 'bg-red-100 text-red-600',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return static::statusColors()[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    // ── Relations ──────────────────────────────────────────────────

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function settler()
    {
        return $this->belongsTo(Admin::class, 'settled_by');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'settlement_approved_by');
    }

    public function dispatches()
    {
        return $this->hasMany(InventoryDispatch::class);
    }

    public function saleOrders()
    {
        return $this->hasMany(SaleOrder::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function bookingRequests()
    {
        return $this->hasMany(TripBookingRequest::class);
    }

    public function custodyTreasury()
    {
        return $this->belongsTo(Treasury::class, 'cash_custody_treasury_id');
    }

    // ── Computed helpers ───────────────────────────────────────────

    public function syncTotals(): void
    {
        $this->total_dispatched_value = $this->dispatches()->sum('total_cost');
        $this->total_invoiced         = $this->saleOrders()->whereNotIn('status', ['cancelled'])->sum('total');
        $this->total_collected        = $this->collections()->where('status', 'completed')->sum('total_amount');
        $this->total_returned_value   = $this->saleReturns()->whereNotIn('status', ['cancelled'])->sum('refund_amount');
        $this->save();
    }

    public function getOutstandingAttribute(): float
    {
        return max(0, (float)$this->total_invoiced - (float)$this->total_collected);
    }

    public function hasCashDeficit(): bool
    {
        return $this->status === 'settled' && $this->settlement_cash_deficit > 0;
    }

    public function hasProductDeficit(): bool
    {
        return $this->status === 'settled' && $this->settlement_product_deficit > 0;
    }
}
