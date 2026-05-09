<?php

namespace App\Livewire\Trips;

use App\Models\Branch;
use App\Models\Delegate;
use App\Models\InventoryDispatch;
use App\Models\InventoryDispatchItem;
use App\Models\Product;
use App\Models\Trip;
use App\Models\TripBookingRequest;
use Livewire\Component;
use Livewire\WithPagination;

class BookingRequestIndex extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $statusFilter   = '';
    public string $delegateFilter = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'statusFilter'   => ['except' => ''],
        'delegateFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function updateStatus(int $id, string $status): void
    {
        $req = TripBookingRequest::with('items.product')->findOrFail($id);

        // When confirming — ensure the request has a trip assigned
        if ($status === 'confirmed') {
            $trip = $this->resolveOrCreateTrip($req);
            if (!$req->trip_id) {
                $req->update(['trip_id' => $trip->id]);
                $req->refresh();
            }
            $req->update(['status' => $status]);

            // Create a dispatch order for the confirmed items
            if ($req->items->isNotEmpty()) {
                $expectedSales = $req->items->sum(fn($item) => $item->quantity * $item->unit_price);

                $dispatch = InventoryDispatch::create([
                    'branch_id'      => $trip->branch_id,
                    'delegate_id'    => $req->delegate_id,
                    'admin_id'       => auth('admin')->id(),
                    'trip_id'        => $trip->id,
                    'status'         => 'pending',
                    'total_cost'     => 0,
                    'expected_sales' => $expectedSales,
                    'date'           => now()->toDateString(),
                    'notes'          => 'من طلب حجز #' . $req->id . ($req->customer_name ? ' - ' . $req->customer_name : ''),
                ]);

                foreach ($req->items as $item) {
                    $costPrice = optional(Product::find($item->product_id))->cost_price ?? 0;
                    InventoryDispatchItem::create([
                        'inventory_dispatch_id' => $dispatch->id,
                        'product_id'            => $item->product_id,
                        'quantity'              => $item->quantity,
                        'cost_price'            => $costPrice,
                        'selling_price'         => $item->unit_price,
                    ]);
                }
            }

            session()->flash('success', 'تم قبول الطلب وإنشاء أمر الصرف بنجاح');
            return;
        }

        $req->update(['status' => $status]);
        session()->flash('success', 'تم تحديث حالة الطلب');
    }

    /**
     * Find the delegate's active trip, or create a new draft trip for them.
     */
    private function resolveOrCreateTrip(TripBookingRequest $req): Trip
    {
        // If already linked to a trip, return it
        if ($req->trip_id) {
            return Trip::findOrFail($req->trip_id);
        }

        $delegateId = $req->delegate_id;

        // Look for an active/draft trip
        $trip = Trip::where('delegate_id', $delegateId)
            ->whereIn('status', ['active', 'in_transit', 'draft'])
            ->latest()
            ->first();

        if ($trip) {
            return $trip;
        }

        // No trip — create one using the delegate's last known branch (or first branch as fallback)
        $branchId = Trip::where('delegate_id', $delegateId)
            ->latest()
            ->value('branch_id')
            ?? Branch::orderBy('id')->value('id');

        return Trip::create([
            'delegate_id' => $delegateId,
            'branch_id'   => $branchId,
            'status'      => 'draft',
            'start_date'  => now()->toDateString(),
        ]);
    }

    public function render()
    {
        $requests = TripBookingRequest::with(['delegate', 'trip'])
            ->when($this->search, fn($q) => $q->where(function ($q2) {
                $q2->where('customer_name', 'like', "%{$this->search}%")
                   ->orWhere('customer_phone', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->delegateFilter, fn($q) => $q->where('delegate_id', $this->delegateFilter))
            ->latest()
            ->paginate(15);

        $delegates    = Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $statusLabels = TripBookingRequest::statusLabels();

        return view('livewire.trips.booking-request-index', compact('requests', 'delegates', 'statusLabels'));
    }
}
