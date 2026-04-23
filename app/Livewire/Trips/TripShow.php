<?php

namespace App\Livewire\Trips;

use App\Models\InventoryDispatch;
use App\Models\InventoryDispatchItem;
use Illuminate\Support\Facades\DB;
use App\Models\Treasury;
use App\Models\Trip;
use App\Models\TripBookingRequest;
use Livewire\Component;

class TripShow extends Component
{
    public Trip $trip;
    public int  $tripId;
    public string $activeTab = 'overview';

    // Cash custody form
    public string $custodyAmount    = '';
    public int    $custodyTreasuryId = 0;
    public string $custodyNote      = '';
    public bool   $showCustodyForm  = false;

    // Quick booking request form
    public string $bqCustomerName    = '';
    public string $bqCustomerPhone   = '';
    public string $bqCustomerAddress = '';
    public string $bqNotes           = '';
    public bool   $showBqForm        = false;

    // Viewing a booking request detail
    public ?int $viewingBrId = null;

    public function mount(int $id): void
    {
        $this->tripId = $id;
        $this->loadTrip();
        // Pre-fill custody fields from existing values
        $this->custodyAmount     = $this->trip->cash_custody_amount > 0
            ? (string)$this->trip->cash_custody_amount
            : '';
        $this->custodyTreasuryId = (int)($this->trip->cash_custody_treasury_id ?? 0);
        $this->custodyNote       = $this->trip->cash_custody_note ?? '';
    }

    protected function loadTrip(): void
    {
        $this->trip = Trip::with(['delegate', 'branch', 'admin', 'custodyTreasury'])->findOrFail($this->tripId);
        $this->trip->syncTotals();
    }

    public function saveCustody(): void
    {
        $this->validate([
            'custodyAmount'     => 'required|numeric|min:0',
            'custodyTreasuryId' => 'required|exists:treasuries,id',
        ]);

        $this->trip->update([
            'cash_custody_amount'      => (float)$this->custodyAmount,
            'cash_custody_treasury_id' => $this->custodyTreasuryId,
            'cash_custody_note'        => $this->custodyNote ?: null,
        ]);

        $this->showCustodyForm = false;
        $this->loadTrip();
        session()->flash('success', 'تم حفظ بيانات العهدة النقدية');
    }

    public function changeStatus(string $status): void
    {
        $allowed = ['draft', 'active', 'in_transit', 'returning', 'settled', 'cancelled'];
        if (!in_array($status, $allowed)) return;

        if ($status === 'returning' || $status === 'settled') {
            $this->trip->actual_return_date = now()->toDateString();
        }
        $this->trip->status = $status;
        $this->trip->save();
        $this->loadTrip();
        session()->flash('success', 'تم تحديث حالة الرحلة');
    }

    public function addBookingRequest(): void
    {
        $this->validate([
            'bqCustomerName'  => 'nullable|string|max:255',
            'bqCustomerPhone' => 'nullable|string|max:30',
        ]);

        TripBookingRequest::create([
            'trip_id'          => $this->tripId,
            'delegate_id'      => $this->trip->delegate_id,
            'customer_name'    => $this->bqCustomerName ?: 'غير محدد',
            'customer_phone'   => $this->bqCustomerPhone,
            'customer_address' => $this->bqCustomerAddress,
            'notes'            => $this->bqNotes,
            'status'           => 'pending',
        ]);

        $this->reset('bqCustomerName', 'bqCustomerPhone', 'bqCustomerAddress', 'bqNotes', 'showBqForm');
        $this->loadTrip();
        session()->flash('success', 'تم إضافة طلب الحجز');
    }

    public function viewBooking(int $id): void
    {
        $this->viewingBrId = ($this->viewingBrId === $id) ? null : $id;
    }

    public function updateBookingStatus(int $id, string $status): void
    {
        $req = TripBookingRequest::with('items')->findOrFail($id);
        $req->update(['status' => $status]);

        if ($status === 'confirmed' && $req->items->isNotEmpty()) {
            $expectedSales = $req->items->sum(fn($item) => $item->quantity * $item->unit_price);

            $dispatch = InventoryDispatch::create([
                'branch_id'      => $this->trip->branch_id,
                'delegate_id'    => $req->delegate_id ?? $this->trip->delegate_id,
                'admin_id'       => auth('admin')->id(),
                'trip_id'        => $this->trip->id,
                'status'         => 'pending',
                'total_cost'     => 0,
                'expected_sales' => $expectedSales,
                'date'           => now()->toDateString(),
                'notes'          => 'من طلب حجز #' . $req->id . ($req->customer_name && $req->customer_name !== 'غير محدد' ? ' - ' . $req->customer_name : ''),
            ]);

            foreach ($req->items as $item) {
                InventoryDispatchItem::create([
                    'inventory_dispatch_id' => $dispatch->id,
                    'product_id'            => $item->product_id,
                    'quantity'              => $item->quantity,
                    'cost_price'            => 0,
                    'selling_price'         => $item->unit_price,
                ]);
            }

            session()->flash('success', 'تم قبول الطلب وإنشاء أمر الصرف بنجاح');
        } else {
            session()->flash('success', 'تم تحديث الطلب');
        }

        $this->viewingBrId = null;
        $this->loadTrip();
    }

    public function confirmDispatch(int $id): void
    {
        $dispatch = InventoryDispatch::with('items')->findOrFail($id);

        if ($dispatch->status !== 'pending') return;

        $totalCost = 0;
        foreach ($dispatch->items as $item) {
            // Deduct from branch stock
            \Illuminate\Support\Facades\DB::table('branch_product')
                ->where('branch_id', $dispatch->branch_id)
                ->where('product_id', $item->product_id)
                ->decrement('quantity', $item->quantity);

            $totalCost += $item->cost_price * $item->quantity;
        }

        $dispatch->update([
            'status'     => 'dispatched',
            'total_cost' => $totalCost,
        ]);

        $this->trip->syncTotals();
        session()->flash('success', 'تم تأكيد أمر الصرف وخصم الكميات من المخزون');
        $this->loadTrip();
    }

    // ── Settlement Approval ──────────────────────────────────────────

    public string $rejectionReason = '';

    public function approveSettlement(): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('trips.approve-settlement')) {
            session()->flash('error', 'ليس لديك صلاحية اعتماد التسوية');
            return;
        }

        DB::transaction(function () use ($admin) {
            $this->trip->update([
                'status'                      => 'settled',
                'settlement_status'           => 'approved',
                'settlement_approved_by'      => $admin->id,
                'settlement_approved_at'      => now(),
                'settlement_rejection_reason' => null,
            ]);

            // Reload trip with dispatches to return stock
            $trip = Trip::with(['dispatches.items', 'saleOrders.items'])->find($this->tripId);
            $branchId = $trip->branch_id;

            // Build product received map from settlement data (expected_remaining = what should come back)
            $rows = [];
            foreach ($trip->dispatches as $dispatch) {
                foreach ($dispatch->items as $item) {
                    $pid = $item->product_id;
                    if (!isset($rows[$pid])) {
                        $rows[$pid] = ['dispatched' => 0, 'sold' => 0, 'already_returned' => 0];
                    }
                    $rows[$pid]['dispatched']       += (float)$item->quantity;
                    $rows[$pid]['already_returned'] += (float)($item->returned_quantity ?? 0);
                }
            }
            foreach ($trip->saleOrders as $order) {
                if ($order->status === 'cancelled') continue;
                foreach ($order->items as $item) {
                    if (isset($rows[$item->product_id])) {
                        $rows[$item->product_id]['sold'] += (float)$item->quantity;
                    }
                }
            }

            // Return remaining stock to branch
            foreach ($rows as $pid => $row) {
                $expectedRemaining = max(0, $row['dispatched'] - $row['sold'] - $row['already_returned']);
                if ($expectedRemaining > 0 && $branchId) {
                    DB::table('branch_product')->updateOrInsert(
                        ['branch_id' => $branchId, 'product_id' => $pid],
                        [
                            'quantity'   => DB::raw("COALESCE(quantity, 0) + {$expectedRemaining}"),
                            'updated_at' => now(),
                            'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                        ]
                    );
                }
            }

            // Mark all linked dispatches as settled
            $trip->dispatches()->update(['status' => 'settled']);
        });

        session()->flash('success', 'تمت الموافقة على التسوية واعتمادها');
        $this->loadTrip();
    }

    public function rejectSettlement(): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('trips.approve-settlement')) {
            session()->flash('error', 'ليس لديك صلاحية رفض التسوية');
            return;
        }

        $this->validate(['rejectionReason' => 'required|string|min:5|max:500'], [
            'rejectionReason.required' => 'سبب الرفض مطلوب',
            'rejectionReason.min'      => 'يجب أن يكون السبب 5 أحرف على الأقل',
        ]);

        $this->trip->update([
            'status'                      => 'active',
            'settlement_status'           => 'rejected',
            'settlement_rejection_reason' => $this->rejectionReason,
            'settlement_approved_by'      => $admin->id,
            'settlement_approved_at'      => now(),
        ]);

        $this->rejectionReason = '';
        session()->flash('success', 'تم رفض التسوية وإعادة الرحلة للحالة النشطة');
        $this->loadTrip();
    }

    public function reopenSettlement(): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('trips.approve-settlement')) {
            session()->flash('error', 'ليس لديك صلاحية إعادة فتح التسوية');
            return;
        }

        $this->trip->update([
            'status'                      => 'active',
            'settlement_status'           => null,
            'settlement_rejection_reason' => null,
            'settlement_approved_by'      => null,
            'settlement_approved_at'      => null,
            'settled_by'                  => null,
            'settled_at'                  => null,
            'settlement_cash_deficit'     => 0,
            'settlement_product_deficit'  => 0,
        ]);

        // Re-open dispatches to their previous state
        $this->trip->dispatches()->where('status', 'settled')->update(['status' => 'dispatched']);

        session()->flash('success', 'تمت إعادة فتح الرحلة للتسوية من جديد');
        $this->loadTrip();
    }

    public function render()
    {
        $dispatches     = $this->trip->dispatches()->with(['branch', 'items.product.unit'])->latest()->get();
        $saleOrders     = $this->trip->saleOrders()->with(['customer', 'items'])->latest()->get();
        $collections    = $this->trip->collections()->with('customer')->latest()->get();
        $saleReturns    = $this->trip->saleReturns()->with('customer')->latest()->get();
        $bookingRequests = $this->trip->bookingRequests()->with(['delegate', 'items.product.unit', 'items.unit'])->latest()->get();
        $treasuries     = Treasury::where('is_active', true)->orderBy('name')->get(['id', 'name', 'balance']);

        return view('livewire.trips.trip-show', compact(
            'dispatches', 'saleOrders', 'collections', 'saleReturns', 'bookingRequests', 'treasuries'
        ));
    }
}

