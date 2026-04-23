<?php

namespace App\Livewire\Trips;

use App\Models\Delegate;
use App\Models\Product;
use App\Models\Trip;
use App\Models\TripBookingRequest;
use App\Models\TripBookingRequestItem;
use App\Models\Unit;
use Livewire\Component;

class BookingRequestForm extends Component
{
    public ?int $tripId   = null;
    public ?int $branchId = null;

    public string $customerName    = '';
    public string $customerPhone   = '';
    public string $customerAddress = '';
    public string $notes           = '';
    public int    $delegateId      = 0;

    public array $items = [];

    public function mount(?int $tripId = null): void
    {
        $this->tripId = $tripId;
        if ($tripId) {
            $trip = Trip::findOrFail($tripId);
            $this->delegateId = $trip->delegate_id;
            $this->branchId   = $trip->branch_id;
        }
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id'        => '',
            'quantity'          => '1',
            'unit_id'           => '',
            'unit_symbol'       => '',
            'unit_price'        => '0',
            'base_selling_price'=> '0',
            'max_quantity'      => '0',
            'available_units'   => [],
            'stock_unit_id'     => '',
            'stock_unit_factor' => '1',
            'tax_rate'          => '0',
            'tax_name'          => '',
            'notes'             => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) return;

        $index = (int) $parts[0];
        $field = $parts[1];

        if (!isset($this->items[$index])) return;

        if ($field === 'product_id' && $value) {
            $product = Product::with(['unit', 'tax', 'branches' => function ($q) {
                $q->where('branch_id', $this->branchId);
            }])->find($value);

            if ($product && $product->unit) {
                $stockUnit      = $product->unit;
                $stockQty       = (int) ($product->branches->first()?->pivot?->quantity ?? 0);
                $availableUnits = $this->getBookingUnits($stockUnit);

                $this->items[$index]['unit_id']            = (string) $stockUnit->id;
                $this->items[$index]['unit_symbol']        = $stockUnit->symbol ?? '';
                $this->items[$index]['max_quantity']       = (string) $stockQty;
                $this->items[$index]['available_units']    = $availableUnits;
                $this->items[$index]['stock_unit_id']      = (string) $stockUnit->id;
                $this->items[$index]['stock_unit_factor']  = (string) $stockUnit->conversion_factor;
                $this->items[$index]['unit_price']         = (string) round((float) ($product->selling_price ?? 0), 2);
                $this->items[$index]['base_selling_price'] = (string) round((float) ($product->selling_price ?? 0), 2);
                $this->items[$index]['tax_rate']           = (string) ($product->tax ? (float) $product->tax->rate : 0);
                $this->items[$index]['tax_name']           = $product->tax ? $product->tax->name : '';
                $this->items[$index]['quantity']           = '1';
            }
            return;
        }

        if ($field === 'unit_id') {
            $this->applySelectedUnitContext($index);
            return;
        }

        if ($field === 'quantity') {
            $max = (int) ($this->items[$index]['max_quantity'] ?? 0);
            $qty = (int) ($this->items[$index]['quantity'] ?? 0);
            if ($qty < 1) $this->items[$index]['quantity'] = '1';
            elseif ($max > 0 && $qty > $max) $this->items[$index]['quantity'] = (string) $max;
        }
    }

    protected function getBookingUnits(Unit $stockUnit): array
    {
        $rootId = $this->getRootUnitId($stockUnit);

        return Unit::where('is_active', true)
            ->where(function ($q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('base_unit_id', $rootId);
            })
            ->where('conversion_factor', '<=', (float) $stockUnit->conversion_factor)
            ->orderBy('conversion_factor', 'desc')
            ->get(['id', 'name', 'symbol', 'conversion_factor'])
            ->map(fn($u) => [
                'id'     => (string) $u->id,
                'name'   => $u->name,
                'symbol' => $u->symbol,
                'factor' => (float) $u->conversion_factor,
            ])
            ->values()
            ->toArray();
    }

    protected function getRootUnitId(Unit $unit): int
    {
        $current = $unit;
        $hops    = 0;
        while ($current->base_unit_id && $hops < 10) {
            $parent = Unit::find($current->base_unit_id);
            if (!$parent) break;
            $current = $parent;
            $hops++;
        }
        return (int) $current->id;
    }

    protected function applySelectedUnitContext(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (!$item) return;

        $selectedUnitId = (string) ($item['unit_id'] ?? '');
        $availableUnits = $item['available_units'] ?? [];
        $selectedUnit   = collect($availableUnits)->firstWhere('id', $selectedUnitId);
        if (!$selectedUnit) return;

        $stockFactor    = (float) ($item['stock_unit_factor'] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);

        $product = Product::with(['branches' => function ($q) {
            $q->where('branch_id', $this->branchId);
        }])->find($item['product_id'] ?? null);

        $stockQty = (int) ($product?->branches->first()?->pivot?->quantity ?? 0);

        if ($selectedFactor <= 0 || $stockFactor <= 0) return;

        $maxQtyInSelected = (int) floor(($stockQty * $stockFactor) / $selectedFactor);

        $this->items[$index]['unit_symbol']  = $selectedUnit['symbol'] ?? '';
        $this->items[$index]['max_quantity'] = (string) max(0, $maxQtyInSelected);

        // Recalculate selling price proportionally
        $ratio     = $selectedFactor / $stockFactor;
        $basePrice = (float) ($this->items[$index]['base_selling_price'] ?? $this->items[$index]['unit_price'] ?? 0);
        $this->items[$index]['unit_price'] = (string) round($basePrice * $ratio, 4);

        $currentQty = (int) ($this->items[$index]['quantity'] ?? 0);
        if ($currentQty > $maxQtyInSelected) {
            $this->items[$index]['quantity'] = (string) max(1, $maxQtyInSelected);
        }
    }

    public function save(): void
    {
        $this->validate([
            'customerName'       => 'nullable|string|max:255',
            'customerPhone'      => 'nullable|string|max:30',
            'delegateId'         => 'required|exists:delegates,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.quantity'   => 'nullable|numeric|min:0.001',
        ]);

        $request = TripBookingRequest::create([
            'trip_id'          => $this->tripId,
            'delegate_id'      => $this->delegateId,
            'customer_name'    => $this->customerName ?: 'غير محدد',
            'customer_phone'   => $this->customerPhone,
            'customer_address' => $this->customerAddress,
            'notes'            => $this->notes,
            'status'           => 'pending',
        ]);

        foreach ($this->items as $item) {
            if (!empty($item['product_id'])) {
                TripBookingRequestItem::create([
                    'booking_request_id' => $request->id,
                    'product_id'         => $item['product_id'],
                    'quantity'           => $item['quantity'] ?? 1,
                    'unit_id'            => $item['unit_id'] ?: null,
                    'unit_price'         => $item['unit_price'] ?? 0,
                    'notes'              => $item['notes'] ?? null,
                ]);
            }
        }

        session()->flash('success', 'تم إنشاء طلب الحجز بنجاح');

        if ($this->tripId) {
            $this->redirect(route('trips.show', $this->tripId), navigate: true);
        } else {
            $this->redirect(route('trips.booking-requests'), navigate: true);
        }
    }

    public function render()
    {
        $delegates = Delegate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products  = Product::with(['unit', 'branches' => fn($q) => $q->where('branch_id', $this->branchId)])
            ->where('is_active', true)->orderBy('name')->get();
        $trips     = Trip::whereIn('status', ['draft', 'active', 'in_transit'])->latest()->get(['id', 'trip_number']);

        return view('livewire.trips.booking-request-form', compact('delegates', 'products', 'trips'));
    }
}
