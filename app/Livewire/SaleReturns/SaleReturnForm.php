<?php

namespace App\Livewire\SaleReturns;

use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\SaleReturnItem;
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

    public float $loaded_order_subtotal = 0;
    public float $loaded_order_discount_amount = 0;
    public float $loaded_order_discount_value = 0;
    public float $loaded_discount_ratio = 0;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSaleOrderId($value)
    {
        $this->resetLoadedOrder();

        if (!$value) {
            return;
        }

        $order = SaleOrder::with([
            'items.product',
            'items.unit.baseUnit',
            'customer',
        ])->find($value);

        if (!$order || !in_array($order->status, ['confirmed', 'partial_paid', 'paid'])) {
            return;
        }

        $this->loaded_customer_id = $order->customer_id;
        $this->loaded_branch_id = $order->branch_id;
        $this->loaded_customer_name = $order->customer?->name;
        $this->loaded_order_number = $order->order_number;

        $this->loaded_order_subtotal = (float) ($order->subtotal ?: $this->calculateOrderItemsSubtotal($order));
        $this->loaded_order_discount_amount = (float) ($order->discount_amount ?? 0);
        $this->loaded_order_discount_value = $this->calculateOrderDiscountValue($order);
        $this->loaded_discount_ratio = $this->loaded_order_subtotal > 0
            ? min(1, $this->loaded_order_discount_value / $this->loaded_order_subtotal)
            : 0;

        $alreadyReturned = SaleReturnItem::whereIn('sale_order_item_id', $order->items->pluck('id'))
            ->whereHas('saleReturn', fn ($q) => $q->whereNotIn('status', ['cancelled']))
            ->selectRaw('sale_order_item_id, SUM(quantity) as total_returned')
            ->groupBy('sale_order_item_id')
            ->pluck('total_returned', 'sale_order_item_id');

        foreach ($order->items as $item) {
            $orderUnit = $item->unit ?: $item->product?->unit;

            if (!$orderUnit) {
                continue;
            }

            $alreadyReturnedQty = (float) ($alreadyReturned[$item->id] ?? 0);
            $remainingQty = max(0, (float) $item->quantity - $alreadyReturnedQty);

            if ($remainingQty <= 0) {
                continue;
            }

            $originalUnitPrice = (float) $item->unit_price;
            $discountPerUnit = $originalUnitPrice * $this->loaded_discount_ratio;
            $netUnitPrice = max(0, $originalUnitPrice - $discountPerUnit);

            $availableUnits = $this->getReturnableUnits($orderUnit);

            $this->items[] = [
                'sale_order_item_id' => (string) $item->id,
                'product_id' => (string) $item->product_id,
                'product_name' => $item->product?->name,

                'unit_id' => (string) $orderUnit->id,
                'unit_symbol' => $orderUnit->symbol ?? '',
                'order_unit_symbol' => $orderUnit->symbol ?? '',

                'original_qty' => (string) $remainingQty,
                'max_quantity' => (string) $remainingQty,
                'quantity' => '0',

                'original_unit_price' => (string) round($originalUnitPrice, 6),
                'discount_per_unit' => (string) round($discountPerUnit, 6),
                'unit_price' => (string) round($netUnitPrice, 6),

                'order_unit_id' => (string) $orderUnit->id,
                'order_unit_factor' => (string) $orderUnit->conversion_factor,
                'order_original_unit_price' => (string) round($originalUnitPrice, 6),
                'order_unit_price' => (string) round($netUnitPrice, 6),

                'available_units' => $availableUnits,
                'reason' => '',
            ];
        }
    }

    protected function resetLoadedOrder(): void
    {
        $this->items = [];
        $this->loaded_customer_id = null;
        $this->loaded_branch_id = null;
        $this->loaded_customer_name = null;
        $this->loaded_order_number = null;

        $this->loaded_order_subtotal = 0;
        $this->loaded_order_discount_amount = 0;
        $this->loaded_order_discount_value = 0;
        $this->loaded_discount_ratio = 0;
    }

    protected function calculateOrderItemsSubtotal(SaleOrder $order): float
    {
        return (float) $order->items->sum(function ($item) {
            return (float) $item->quantity * (float) $item->unit_price;
        });
    }

    protected function calculateOrderDiscountValue(SaleOrder $order): float
    {
        $subtotal = (float) ($order->subtotal ?: $this->calculateOrderItemsSubtotal($order));
        $discount = (float) ($order->discount_amount ?? 0);
        $type = strtolower((string) ($order->discount_type ?? ''));

        if ($discount <= 0 || $subtotal <= 0) {
            return 0;
        }

        if (in_array($type, ['percentage', 'percent', '%'])) {
            return round($subtotal * ($discount / 100), 6);
        }

        return min($discount, $subtotal);
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);

        if (count($parts) !== 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if (!isset($this->items[$index])) {
            return;
        }

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
                $q->where('id', $rootId)
                    ->orWhere('base_unit_id', $rootId);
            })
            ->where('conversion_factor', '<=', (float) $orderUnit->conversion_factor)
            ->orderBy('conversion_factor', 'desc')
            ->get(['id', 'name', 'symbol', 'conversion_factor'])
            ->map(fn ($u) => [
                'id' => (string) $u->id,
                'name' => $u->name,
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

            if (!$parent) {
                break;
            }

            $current = $parent;
            $hops++;
        }

        return (int) $current->id;
    }

    protected function applySelectedUnitContext(int $index): void
    {
        $item = $this->items[$index] ?? null;

        if (!$item) {
            return;
        }

        $selectedUnitId = (string) ($item['unit_id'] ?? '');
        $selectedUnit = collect($item['available_units'] ?? [])
            ->firstWhere('id', $selectedUnitId);

        if (!$selectedUnit) {
            return;
        }

        $orderFactor = (float) ($item['order_unit_factor'] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);

        $netOrderUnitPrice = (float) ($item['order_unit_price'] ?? 0);
        $originalOrderUnitPrice = (float) ($item['order_original_unit_price'] ?? 0);
        $originalQty = (float) ($item['original_qty'] ?? 0);

        if ($orderFactor <= 0 || $selectedFactor <= 0) {
            return;
        }

        $maxQtyInSelected = floor(($originalQty * $orderFactor) / $selectedFactor);

        $netUnitPriceInSelected = $netOrderUnitPrice * ($selectedFactor / $orderFactor);
        $originalUnitPriceInSelected = $originalOrderUnitPrice * ($selectedFactor / $orderFactor);
        $discountPerUnitInSelected = max(0, $originalUnitPriceInSelected - $netUnitPriceInSelected);

        $this->items[$index]['unit_symbol'] = $selectedUnit['symbol'] ?? '';
        $this->items[$index]['max_quantity'] = (string) max(0, $maxQtyInSelected);
        $this->items[$index]['original_unit_price'] = (string) round($originalUnitPriceInSelected, 6);
        $this->items[$index]['discount_per_unit'] = (string) round($discountPerUnitInSelected, 6);
        $this->items[$index]['unit_price'] = (string) round($netUnitPriceInSelected, 6);

        $currentQty = (float) ($this->items[$index]['quantity'] ?? 0);

        if ($currentQty > $maxQtyInSelected) {
            $this->items[$index]['quantity'] = (string) max(0, $maxQtyInSelected);
        }
    }

    public function getCalculatedTotalsProperty(): array
    {
        $subtotalBeforeDiscount = 0;
        $discountTotal = 0;
        $refund = 0;

        foreach ($this->items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $originalPrice = (float) ($item['original_unit_price'] ?? 0);
            $discountPerUnit = (float) ($item['discount_per_unit'] ?? 0);
            $netPrice = (float) ($item['unit_price'] ?? 0);

            $subtotalBeforeDiscount += $qty * $originalPrice;
            $discountTotal += $qty * $discountPerUnit;
            $refund += $qty * $netPrice;
        }

        return [
            'subtotal_before_discount' => round($subtotalBeforeDiscount, 2),
            'discount_amount' => round($discountTotal, 2),
            'subtotal' => round($refund, 2),
            'refund' => round($refund, 2),
        ];
    }

    protected function rules(): array
    {
        return [
            'sale_order_id' => 'required|exists:sale_orders,id',
            'date' => 'required|date',
            'treasury_id' => 'nullable|exists:treasuries,id',
            'notes' => 'nullable|string|max:1000',

            'items' => 'required|array|min:1',
            'items.*.sale_order_item_id' => 'required|exists:sale_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }

    public function save(SaleReturnService $service)
    {
        $this->validate();

        $returnItems = collect($this->items)
            ->filter(fn ($item) => ((float) ($item['quantity'] ?? 0)) > 0)
            ->values()
            ->toArray();

        if (empty($returnItems)) {
            session()->flash('error', 'يجب إدخال كمية مرتجعة لمنتج واحد على الأقل');
            return;
        }

        foreach ($returnItems as $ri) {
            $qty = (float) ($ri['quantity'] ?? 0);
            $maxQty = (float) ($ri['max_quantity'] ?? 0);

            if ($maxQty > 0 && $qty > $maxQty) {
                session()->flash('error', 'كمية الإرجاع تتجاوز الحد المسموح للوحدة المختارة');
                return;
            }

            $orderItemId = (int) ($ri['sale_order_item_id'] ?? 0);
            $orderItem = SaleOrderItem::with('product')->find($orderItemId);

            if ($orderItem) {
                $alreadyReturned = SaleReturnItem::where('sale_order_item_id', $orderItemId)
                    ->whereHas('saleReturn', fn ($q) => $q->whereNotIn('status', ['cancelled']))
                    ->sum('quantity');

                $remaining = max(0, (float) $orderItem->quantity - (float) $alreadyReturned);

                if ($qty > $remaining) {
                    session()->flash(
                        'error',
                        "الكمية المُرتجعة للمنتج \"{$orderItem->product?->name}\" تتجاوز الكمية المتاحة للإرجاع ({$remaining})"
                    );
                    return;
                }
            }
        }

        $admin = auth('admin')->user();

        try {
            $service->createReturn([
                'sale_order_id' => $this->sale_order_id,
                'customer_id' => $this->loaded_customer_id,
                'branch_id' => $this->loaded_branch_id,
                'admin_id' => $admin?->id,
                'date' => $this->date,
                'treasury_id' => $this->treasury_id,
                'notes' => $this->notes ?: null,
            ], $returnItems);

            session()->flash('success', 'تم إنشاء مرتجع المبيعات بنجاح');

            return redirect()->route('sale-returns.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sale-returns.sale-return-form', [
            'orders' => SaleOrder::whereIn('status', ['confirmed', 'partial_paid', 'paid'])
                ->with('customer')
                ->orderByDesc('created_at')
                ->get(),

            'treasuries' => Treasury::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
}