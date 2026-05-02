<?php

namespace App\Services;

use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function getByDelegate($delegateId)
    {
        return Trip::where('delegate_id', $delegateId)
            ->with(['branch:id,name'])
            ->latest()
            ->get();
    }

    public function getById($id): Trip
    {
        return Trip::with(['branch:id,name', 'custodyTreasury:id,name'])->findOrFail($id);
    }

    public function create(array $data): Trip
    {
        return DB::transaction(function () use ($data) {
            return Trip::create([
                'delegate_id'          => $data['delegate_id'],
                'branch_id'            => $data['branch_id'],
                'status'               => 'draft',
                'start_date'           => $data['start_date'] ?? now()->toDateString(),
                'expected_return_date' => $data['expected_return_date'] ?? null,
                'notes'                => $data['notes'] ?? null,
            ]);
        });
    }

    public function start(Trip $trip): Trip
    {
        if (!in_array($trip->status, ['draft', 'returning'])) {
            throw new \Exception('لا يمكن تشغيل هذه الرحلة في وضعها الحالي');
        }

        $trip->update(['status' => 'active']);
        return $trip->fresh();
    }

    public function end(Trip $trip): Trip
    {
        if (!in_array($trip->status, ['active', 'in_transit'])) {
            throw new \Exception('لا يمكن إنهاء هذه الرحلة في وضعها الحالي');
        }

        $trip->syncTotals();
        $trip->update([
            'status'             => 'returning',
            'actual_return_date' => now()->toDateString(),
        ]);

        return $trip->fresh();
    }
}
