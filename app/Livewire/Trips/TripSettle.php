<?php

namespace App\Livewire\Trips;

use App\Models\InventoryDispatch;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TripSettle extends Component
{
    public Trip  $trip;
    public int   $tripId;

    public float  $cashActual      = 0;
    public string $settlementNotes = '';

    /** @var array<int, array{product_id:int, name:string, unit:string, selling_price:float, dispatched:float, sold:float, already_returned:float, expected_remaining:float, actual_received:string}> */
    public array $productItems = [];

    public function mount(int $id): void
    {
        $this->tripId = $id;
        $this->trip   = Trip::with([
            'delegate', 'branch', 'custodyTreasury',
            'dispatches.items.product.unit',
            'saleOrders.items',
        ])->findOrFail($id);

        $this->trip->syncTotals();
        $this->cashActual = round((float)$this->trip->total_collected + (float)$this->trip->cash_custody_amount, 2);
        $this->buildProductItems();
    }

    private function buildProductItems(): void
    {
        $this->buildProductItemsPublic();
    }

    public function buildProductItemsPublic(): void
    {
        $rows = [];

        // Sum dispatched quantities and already-returned quantities per product
        foreach ($this->trip->dispatches as $dispatch) {
            foreach ($dispatch->items as $item) {
                $pid = $item->product_id;
                if (!isset($rows[$pid])) {
                    $rows[$pid] = [
                        'product_id'         => $pid,
                        'name'               => $item->product?->name ?? '#' . $pid,
                        'unit'               => $item->product?->unit?->name ?? '—',
                        'selling_price'      => (float)$item->selling_price,
                        'dispatched'         => 0.0,
                        'sold'               => 0.0,
                        'already_returned'   => 0.0,
                        'expected_remaining' => 0.0,
                        'actual_received'    => '0',
                    ];
                }
                $rows[$pid]['dispatched']       += (float)$item->quantity;
                $rows[$pid]['already_returned'] += (float)($item->returned_quantity ?? 0);
            }
        }

        // Subtract sold quantities from non-cancelled sale orders on this trip
        foreach ($this->trip->saleOrders as $order) {
            if ($order->status === 'cancelled') continue;
            foreach ($order->items as $item) {
                $pid = $item->product_id;
                if (isset($rows[$pid])) {
                    $rows[$pid]['sold'] += (float)$item->quantity;
                }
            }
        }

        // expected_remaining = dispatched - sold - already_returned (via prior return forms)
        foreach ($rows as $pid => &$row) {
            $row['expected_remaining'] = max(0, $row['dispatched'] - $row['sold'] - $row['already_returned']);
            $row['actual_received']    = (string)round($row['expected_remaining'], 3);
        }
        unset($row);

        $this->productItems = array_values($rows);
    }

    public function settle(): void
    {
        $this->validate([
            'cashActual'      => 'required|numeric|min:0',
            'settlementNotes' => 'nullable|string|max:2000',
            'productItems.*.actual_received' => 'required|numeric|min:0',
        ]);

        $admin       = auth('admin')->user();
        $canApprove  = $admin->hasPermission('trips.approve-settlement');

        $cashExpected = round((float)$this->trip->total_collected + (float)$this->trip->cash_custody_amount, 2);
        $cashDeficit  = max(0, $cashExpected - $this->cashActual);

        // Calculate total product deficit value
        $totalProductDeficit = 0.0;
        foreach ($this->productItems as $item) {
            $deficitQty = max(0, $item['expected_remaining'] - (float)$item['actual_received']);
            $totalProductDeficit += $deficitQty * $item['selling_price'];
        }
        $totalProductDeficit = round($totalProductDeficit, 2);

        DB::transaction(function () use ($admin, $canApprove, $cashExpected, $cashDeficit, $totalProductDeficit) {
            $settlementData = [
                'actual_return_date'         => now()->toDateString(),
                'settlement_cash_expected'   => $cashExpected,
                'settlement_cash_actual'     => $this->cashActual,
                'settlement_cash_deficit'    => $cashDeficit,
                'settlement_product_deficit' => $totalProductDeficit,
                'settlement_notes'           => $this->settlementNotes ?: null,
                'settled_by'                 => $admin->id,
                'settled_at'                 => now(),
            ];

            if ($canApprove) {
                // Auto-approve: finalize immediately
                $settlementData['status']                  = 'settled';
                $settlementData['settlement_status']       = 'approved';
                $settlementData['settlement_approved_by']  = $admin->id;
                $settlementData['settlement_approved_at']  = now();
                $this->trip->update($settlementData);
                $this->finalizeSettlement();
            } else {
                // Needs approval: save data, keep trip active, mark pending
                $settlementData['status']            = 'active';
                $settlementData['settlement_status'] = 'pending';
                $this->trip->update($settlementData);
            }

            // Update delegate deficit stats regardless of approval status
            if ($cashDeficit > 0 || $totalProductDeficit > 0) {
                $delegate  = $this->trip->delegate;
                $deduction = 0;
                if ($cashDeficit > 0) {
                    $deduction += 5;
                    $delegate->increment('total_cash_deficit', $cashDeficit);
                }
                if ($totalProductDeficit > 0) {
                    $deduction += 5;
                    $delegate->increment('total_product_deficit', $totalProductDeficit);
                }
                $delegate->decrement('rating', min($deduction, (float)$delegate->rating));
            }
        });

        if ($canApprove) {
            session()->flash('success', 'تمت تسوية الرحلة بنجاح وتم اعتمادها فوراً');
        } else {
            session()->flash('success', 'تم حفظ بيانات التسوية وهي بانتظار اعتماد المسؤول');
        }
        $this->redirect(route('trips.show', $this->tripId), navigate: true);
    }

    /**
     * Apply settlement: return stock to branch + mark dispatches as settled.
     * Called on auto-approve or when admin approves a pending settlement.
     */
    public function finalizeSettlement(): void
    {
        $branchId = $this->trip->branch_id;

        // Return actual_received quantities back to branch stock
        foreach ($this->productItems as $item) {
            $qty = (float)$item['actual_received'];
            if ($qty > 0 && $branchId) {
                DB::table('branch_product')->updateOrInsert(
                    ['branch_id' => $branchId, 'product_id' => $item['product_id']],
                    [
                        'quantity'   => DB::raw("COALESCE(quantity, 0) + {$qty}"),
                        'updated_at' => now(),
                        'created_at' => DB::raw("COALESCE(created_at, '" . now() . "')"),
                    ]
                );
            }
        }

        // Mark all linked dispatches as settled
        $this->trip->dispatches()->update(['status' => 'settled']);
    }

    public function render()
    {
        $this->trip->syncTotals();
        $this->trip->load('custodyTreasury');
        return view('livewire.trips.trip-settle');
    }
}