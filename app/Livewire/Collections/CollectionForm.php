<?php

namespace App\Livewire\Collections;

use App\Models\Branch;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Customer;
use App\Models\Delegate;
use App\Models\SaleOrder;
use App\Models\Treasury;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CollectionForm extends Component
{
    public string $delegateId      = '';   // optional
    public string $customerId      = '';
    public string $branchId        = '';
    public string $treasuryId      = '';
    public string $collectionDate  = '';
    public string $status          = 'completed';
    public string $notes           = '';
    public string $manualAmount    = '';   // used when no orders linked

    // Order items (optional): array of ['sale_order_id' => X, 'amount' => Y, 'notes' => '']
    public array $items = [];

    // Available orders for selected customer
    public array $availableOrders = [];

    public float $totalAmount = 0;

    public function mount(): void
    {
        $this->collectionDate = now()->toDateString();
    }

    public function updatedDelegateId(): void
    {
        // delegate change doesn't affect orders list
    }

    public function updatedCustomerId(): void
    {
        $this->items = [];
        $this->totalAmount = 0;
        $this->loadAvailableOrders();
    }

    protected function loadAvailableOrders(): void
    {
        if (!$this->customerId) {
            $this->availableOrders = [];
            return;
        }

        $this->availableOrders = SaleOrder::where('customer_id', (int) $this->customerId)
            ->whereIn('status', ['confirmed', 'partial_paid'])
            ->orderByDesc('date')
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'total'        => (float) $o->total,
                'paid_amount'  => (float) $o->paid_amount,
                'remaining'    => round((float) $o->total - (float) $o->paid_amount, 2),
                'date'         => $o->date?->format('Y-m-d'),
            ])
            ->filter(fn($o) => $o['remaining'] > 0)
            ->values()
            ->toArray();
    }

    public function addOrder(int $orderId): void
    {
        // Prevent duplicates
        foreach ($this->items as $item) {
            if ((int) $item['sale_order_id'] === $orderId) return;
        }

        $order = collect($this->availableOrders)->firstWhere('id', $orderId);
        if (!$order) return;

        $this->items[] = [
            'sale_order_id'  => $orderId,
            'order_number'   => $order['order_number'],
            'remaining'      => $order['remaining'],
            'amount'         => $order['remaining'], // default to full remaining
            'notes'          => '',
        ];
        $this->recalcTotal();
    }

    public function removeOrder(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->recalcTotal();
    }

    public function updatedItems(): void
    {
        $this->recalcTotal();
    }

    protected function recalcTotal(): void
    {
        if (!empty($this->items)) {
            $this->totalAmount = round(
                collect($this->items)->sum(fn($i) => (float) ($i['amount'] ?? 0)),
                2
            );
        } else {
            $this->totalAmount = (float) ($this->manualAmount ?: 0);
        }
    }

    public function updatedManualAmount(): void
    {
        if (empty($this->items)) {
            $this->totalAmount = (float) ($this->manualAmount ?: 0);
        }
    }

    public function save(): void
    {
        $rules = [
            'customerId'     => 'required|exists:customers,id',
            'treasuryId'     => 'required|exists:treasuries,id',
            'collectionDate' => 'required|date',
            'status'         => 'required|in:pending,completed,cancelled',
            'delegateId'     => 'nullable|exists:delegates,id',
            'items.*.sale_order_id' => 'nullable|exists:sale_orders,id',
            'items.*.amount' => 'nullable|numeric|min:0.01',
        ];

        // If no order items, require a manual amount
        if (empty($this->items)) {
            $rules['manualAmount'] = 'required|numeric|min:0.01';
        }

        $this->validate($rules, [
            'customerId.required'      => 'العميل مطلوب',
            'treasuryId.required'      => 'الخزينة مطلوبة',
            'collectionDate.required'  => 'التاريخ مطلوب',
            'manualAmount.required'    => 'يجب إدخال المبلغ أو إضافة فاتورة بيع',
            'manualAmount.min'         => 'المبلغ يجب أن يكون أكبر من صفر',
            'items.*.amount.min'       => 'المبلغ يجب أن يكون أكبر من صفر',
        ]);

        // Recalc total before saving
        $this->recalcTotal();

        $admin = auth('admin')->user();

        DB::transaction(function () use ($admin) {
            $collection = Collection::create([
                'delegate_id'     => $this->delegateId ? (int) $this->delegateId : null,
                'customer_id'     => (int) $this->customerId,
                'branch_id'       => $this->branchId ? (int) $this->branchId : null,
                'treasury_id'     => (int) $this->treasuryId,
                'admin_id'        => $admin->id,
                'collection_date' => $this->collectionDate,
                'total_amount'    => $this->totalAmount,
                'status'          => $this->status,
                'notes'           => $this->notes ?: null,
            ]);

            foreach ($this->items as $item) {
                CollectionItem::create([
                    'collection_id' => $collection->id,
                    'sale_order_id' => (int) $item['sale_order_id'],
                    'amount'        => (float) $item['amount'],
                    'notes'         => $item['notes'] ?: null,
                ]);

                // Update the sale order paid_amount and status
                if ($this->status === 'completed') {
                    $order = SaleOrder::find((int) $item['sale_order_id']);
                    if ($order) {
                        // Auto-distribute payment across linked installment plan entries first
                        $plan = \App\Models\InstallmentPlan::where('reference_type', 'sale_order')
                            ->where('reference_id', $order->id)
                            ->where('status', 'active')
                            ->first();

                        if ($plan) {
                            // Distribute the collection amount across pending entries
                            $remaining = (float) $item['amount'];
                            $pendingEntries = $plan->entries()
                                ->whereIn('status', ['pending', 'partial', 'overdue'])
                                ->orderBy('due_date')
                                ->get();

                            foreach ($pendingEntries as $entry) {
                                if ($remaining <= 0) break;
                                $entryRemaining = (float) $entry->amount - (float) $entry->paid_amount;
                                $toPay = min($remaining, $entryRemaining);
                                $newEntryPaid = round((float) $entry->paid_amount + $toPay, 2);
                                $newEntryStatus = $newEntryPaid >= (float) $entry->amount ? 'paid' : 'partial';
                                $entry->update([
                                    'paid_amount' => $newEntryPaid,
                                    'status'      => $newEntryStatus,
                                    'paid_at'     => $this->collectionDate,
                                    'treasury_id' => (int) $this->treasuryId,
                                    'admin_id'    => $admin->id,
                                ]);
                                $remaining -= $toPay;
                            }

                            // Sync sale order paid_amount from plan entries total
                            $totalPaidViaEntries = (float) $plan->entries()->sum('paid_amount') + (float) $plan->down_payment;
                            $newPaid = min(round($totalPaidViaEntries, 2), (float) $order->total);
                            $newStatus = $newPaid >= (float) $order->total ? 'paid' : 'partial_paid';
                            $order->update(['paid_amount' => $newPaid, 'status' => $newStatus]);

                            // Mark plan as completed if all entries are paid
                            $allPaid = $plan->entries()->where('status', '!=', 'paid')->doesntExist();
                            if ($allPaid) {
                                $plan->update(['status' => 'completed']);
                            }
                        } else {
                            // No installment plan — just update the sale order directly
                            $newPaid = round((float) $order->paid_amount + (float) $item['amount'], 2);
                            $newPaid = min($newPaid, (float) $order->total);
                            $newStatus = $newPaid >= (float) $order->total ? 'paid' : 'partial_paid';
                            $order->update(['paid_amount' => $newPaid, 'status' => $newStatus]);
                        }
                    }
                }
            }

            // If no items were linked, distribute manual amount across customer's active installment plans
            if (empty($this->items) && $this->status === 'completed' && $this->customerId && $this->manualAmount > 0) {
                $activePlans = \App\Models\InstallmentPlan::where('party_type', 'customer')
                    ->where('customer_id', (int) $this->customerId)
                    ->where('status', 'active')
                    ->orderBy('created_at')
                    ->get();

                $remaining = (float) $this->manualAmount;
                foreach ($activePlans as $plan) {
                    if ($remaining <= 0) break;
                    $pendingEntries = $plan->entries()
                        ->whereIn('status', ['pending', 'partial', 'overdue'])
                        ->orderBy('due_date')
                        ->get();

                    foreach ($pendingEntries as $entry) {
                        if ($remaining <= 0) break;
                        $entryRemaining = (float) $entry->amount - (float) $entry->paid_amount;
                        $toPay = min($remaining, $entryRemaining);
                        $newEntryPaid = round((float) $entry->paid_amount + $toPay, 2);
                        $newEntryStatus = $newEntryPaid >= (float) $entry->amount ? 'paid' : 'partial';
                        $entry->update([
                            'paid_amount' => $newEntryPaid,
                            'status'      => $newEntryStatus,
                            'paid_at'     => $this->collectionDate,
                            'treasury_id' => (int) $this->treasuryId,
                            'admin_id'    => $admin->id,
                        ]);
                        $remaining -= $toPay;
                    }

                    // Sync plan's linked sale order if any
                    if ($plan->reference_type === 'sale_order' && $plan->reference_id) {
                        $order = \App\Models\SaleOrder::find($plan->reference_id);
                        if ($order) {
                            $totalPaid = (float) $plan->entries()->sum('paid_amount') + (float) $plan->down_payment;
                            $newPaid = min(round($totalPaid, 2), (float) $order->total);
                            $newStatus = $newPaid >= (float) $order->total ? 'paid' : 'partial_paid';
                            $order->update(['paid_amount' => $newPaid, 'status' => $newStatus]);
                        }
                    }

                    $allPaid = $plan->entries()->where('status', '!=', 'paid')->doesntExist();
                    if ($allPaid) {
                        $plan->update(['status' => 'completed']);
                    }
                }
            }
        });

        session()->flash('success', 'تم تسجيل التحصيل بنجاح');
        $this->redirect(route('collections.index'));
    }

    public function render()
    {
        return view('livewire.collections.collection-form', [
            'delegates'  => Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'customers'  => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'branches'   => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
