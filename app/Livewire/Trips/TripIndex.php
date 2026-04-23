<?php

namespace App\Livewire\Trips;

use App\Models\Delegate;
use App\Models\Trip;
use Livewire\Component;
use Livewire\WithPagination;

class TripIndex extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $statusFilter = '';
    public string $delegateFilter = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'statusFilter'   => ['except' => ''],
        'delegateFilter' => ['except' => ''],
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingDelegateFilter(): void { $this->resetPage(); }

    public function render()
    {
        $query = Trip::with(['delegate', 'branch'])
            ->when($this->search, fn($q) => $q->where('trip_number', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->delegateFilter, fn($q) => $q->where('delegate_id', $this->delegateFilter))
            ->when($this->dateFrom, fn($q) => $q->where('start_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('start_date', '<=', $this->dateTo))
            ->latest()
            ->paginate(15);

        $delegates = Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $statusLabels = Trip::statusLabels();

        return view('livewire.trips.trip-index', [
            'trips'        => $query,
            'delegates'    => $delegates,
            'statusLabels' => $statusLabels,
        ]);
    }
}
