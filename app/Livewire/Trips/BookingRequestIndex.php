<?php

namespace App\Livewire\Trips;

use App\Models\Delegate;
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
        $req = TripBookingRequest::findOrFail($id);
        $req->update(['status' => $status]);
        session()->flash('success', 'تم تحديث حالة الطلب');
    }

    public function render()
    {
        $requests = TripBookingRequest::with(['delegate', 'trip'])
            ->when($this->search, fn($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('customer_phone', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->delegateFilter, fn($q) => $q->where('delegate_id', $this->delegateFilter))
            ->latest()
            ->paginate(15);

        $delegates     = Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $statusLabels  = TripBookingRequest::statusLabels();

        return view('livewire.trips.booking-request-index', compact('requests', 'delegates', 'statusLabels'));
    }
}
