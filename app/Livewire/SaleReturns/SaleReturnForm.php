<?php

namespace App\Livewire\SaleReturns;

use App\Models\SaleOrder;
use App\Models\Treasury;
use App\Models\Unit;
use App\Services\SaleReturnService;
use Livewire\Component;

class SaleReturnForm extends Component
{
    public ?int $sale_order_id = null;
    public ?int $treasury_id = null;
    public string $date = '';
    public string $notes = '';
    public array $items = [];

    public ?int $loaded_customer_id = null;
    public ?int $loaded_branch_id = null;
    public ?string $loaded_customer_name = null;
    public ?string $loaded_order_number = null;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSaleOrderId($value)
    {
        $this->items = [];
        $this->loaded_customer_id = null;
        $this->loaded_branch_id = null;
        $this->loaded_customer_name = null;
        $this->loaded_order_number = null;

        if ($value) {
            $order = SaleOrder::with(['items.product', 'items.unit.baseUnit', 'customer'])->find($value);
            if ($order && in_array($order->status, ['confirmed', 'partial_paid', 'paid'])) {
                $this->loaded_customer_id = $order->customer_id;
                $this->loaded_branch_id = $order->branch_id;
                $this->loaded_customer_name = $order->customer->name;
                $this->loaded_order_number = $order->order_number;

                foreach ($order->items as $item) {
                    $orderUnit = $item->unit ?: $item->product?->unit;
                    if (!$orderUnit) continue;

                    $availableUnits = $this->getReturnableUnits($orderUnit);

                    $this->items[] = [
                        'sale_order_item_id'   => (string) $item->id,
                        'product_id'           => (string) $item->product_id,
                        'product_name'         => $item->product->name,
                        'unit_id'              => (string) $orderUnit->id,
                        'unit_symbol'          => $orderUnit->symbol ?? '',
                        'order_unit_symbol'    => $orderUnit->symbol ?? '',
                        'original_qty'         => (string) $item->quantity,
                        'unit_price'           => (string) $item->unit_price,
                        'order_unit_id'        => (string) $orderUnit->id,
                        'order_unit_factor'    => (string) $orderUnit->conversion_factor,
                        'order_unit_price'     => (string) $item->unit_price,
                        'max_quantity'         => (string) $item->quantity,
                        'available_units'      => $availableUnits,
                        'quantity'             => '0',
                        'reason'               => '',
                    ];
                }
            }
        }
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) return;

        $index = (int) $parts[0];
        $field = $parts[1];

        if (!isset($this->items[$index])) return;

        if ($field === 'unit_id') {
            $this->applySelectedUnitContext($index);
            return;
        }

        if ($field === 'quantity') {
            $max = (float) ($this->items[$index]['max_quantity'] ?? 0);
            $qty = (float) ($this->items[$index]['quantity'] ?? 0);
            if ($qty < 0) {
                $this->items[$index]['quantity'] = '0';
            } elseif ($max > 0 && $qty > $max) {
                $this->items[$index]['quantity'] = (string) $max;
            }
        }
    }

    protected function getReturnableUnits(Unit $orderUnit): array
    {
        $rootId = $this->getRootUnitId($orderUnit);

        return Unit::where('is_active', true)
            ->where(function ($q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('base_unit_id', $rootId);
            })
            ->where('conversion_factor', '<=', (float) $orderUnit->conversion_factor)
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
        $hops = 0;
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
        $selectedUnit = collect($availableUnits)->firstWhere('id', $selectedUnitId);
        if (!$selectedUnit) return;

        $orderFactor    = (float) ($item['order_unit_factor'] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);
        $orderUnitPrice = (float) ($item['order_unit_price'] ?? 0);
        $originalQty    = (float) ($item['original_qty'] ?? 0);

        if ($orderFactor <= 0 || $selectedFactor <= 0) return;

        $maxQtyInSelected    = floor(($originalQty * $orderFactor) / $selectedFactor);
        $unitPriceInSelected = $orderUnitPrice * ($selectedFactor / $orderFactor);

        $this->items[$index]['unit_symbol']  = $selectedUnit['symbol'] ?? '';
        $this->items[$index]['max_quantity'] = (string) max(0, $maxQtyInSelected);
        $this->items[$index]['unit_price']   = (string) round($unitPriceInSelected, 6);

        $currentQty = (float) ($this->items[$index]['quantity'] ?? 0);
        if ($currentQty > $maxQtyInSelected) {
            $this->items[$index]['quantity'] = (string) max(0, $maxQtyInSelected);
        }
    }

    public function getCalculatedTotalsProperty(): array
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $qty   = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $subtotal += $qty * $price;
        }
        return [
            'subtotal' => round($subtotal, 2),
            'refund'   => round($subtotal, 2),
        ];
    }

    protected function rules(): array
    {
        return [
            'sale_order_id'          => 'required|exists:sale_orders,id',
            'date'                   => 'required|date',
            'treasury_id'            => 'nullable|exists:treasuries,id',
            'notes'                  => 'nullable|string|max:1000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:0',
            'items.*.unit_id'        => 'nullable|exists:units,id',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.reason'         => 'nullable|string|max:255',
        ];
    }

    public function save(SaleReturnService $service)
    {
        $this->validate();

        $returnItems = collect($this->items)
            ->filter(fn($item) => ((float) ($item['quantity'] ?? 0)) > 0)
            ->values()
            ->toArray();

        if (empty($returnItems)) {
            session()->flash('error', 'يجب إدخال كمية مرتجعة لمنتج واحد على الأقل');
            return;
        }

        foreach ($returnItems as $ri) {
            $qty    = (float) ($ri['quantity'] ?? 0);
            $maxQty = (float) ($ri['max_quantity'] ?? 0);
            if ($maxQty > 0 && $qty > $maxQty) {
                session()->flash('error', 'كمية الإرجاع تتجاوز الحد المسموح للوحدة المختارة');
                return;
            }
        }

        $admin = auth('admin')->user();

        try {
            $service->createReturn([
                'sale_order_id' => $this->sale_order_id,
                'customer_id'   => $this->loaded_customer_id,
                'branch_id'     => $this->loaded_branch_id,
                'admin_id'      => $admin->id,
                'date'          => $this->date,
                'treasury_id'   => $this->treasury_id,
                'notes'         => $this->notes ?: null,
            ], $returnItems);

            session()->flash('success', 'تم إنشاء مرتجع المبيعات بنجاح');
            return redirect()->route('sale-returns.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $orders = SaleOrder::whereIn('status', ['confirmed', 'partial_paid', 'paid'])
            ->with('customer')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.sale-returns.sale-return-form', [
            'orders'     => $orders,
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
