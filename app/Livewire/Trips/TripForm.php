<?php

namespace App\Livewire\Trips;

use App\Models\Branch;
use App\Models\Delegate;
use App\Models\Trip;
use Livewire\Component;

class TripForm extends Component
{
    public ?int $tripId = null;

    public int    $delegateId       = 0;
    public int    $branchId         = 0;
    public string $status           = 'draft';
    public string $startDate        = '';
    public string $expectedReturnDate = '';
    public string $notes            = '';

    public function mount(?int $id = null): void
    {
        $this->startDate = now()->format('Y-m-d');

        if ($id) {
            $trip = Trip::findOrFail($id);
            $this->tripId              = $id;
            $this->delegateId          = $trip->delegate_id;
            $this->branchId            = $trip->branch_id;
            $this->status              = $trip->status;
            $this->startDate           = $trip->start_date->format('Y-m-d');
            $this->expectedReturnDate  = $trip->expected_return_date?->format('Y-m-d') ?? '';
            $this->notes               = $trip->notes ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'delegateId'         => 'required|exists:delegates,id',
            'branchId'           => 'required|exists:branches,id',
            'status'             => 'required|in:draft,active,in_transit,returning,settled,cancelled',
            'startDate'          => 'required|date',
            'expectedReturnDate' => 'nullable|date|after_or_equal:startDate',
            'notes'              => 'nullable|string|max:2000',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'delegate_id'          => $this->delegateId,
            'branch_id'            => $this->branchId,
            'status'               => $this->status,
            'start_date'           => $this->startDate,
            'expected_return_date' => $this->expectedReturnDate ?: null,
            'notes'                => $this->notes ?: null,
            'admin_id'             => auth('admin')->id(),
        ];

        if ($this->tripId) {
            Trip::findOrFail($this->tripId)->update($payload);
            session()->flash('success', 'تم تحديث الرحلة بنجاح');
        } else {
            $trip = Trip::create($payload);
            session()->flash('success', 'تم إنشاء الرحلة بنجاح');
            $this->redirect(route('trips.show', $trip->id), navigate: true);
            return;
        }

        $this->redirect(route('trips.show', $this->tripId), navigate: true);
    }

    public function render()
    {
        $delegates   = Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $branches    = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $statusLabels = Trip::statusLabels();

        return view('livewire.trips.trip-form', compact('delegates', 'branches', 'statusLabels'));
    }
}
