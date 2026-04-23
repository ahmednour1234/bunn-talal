<?php

namespace App\Livewire\InventoryDispatches;

use App\Models\Branch;
use App\Models\Delegate;
use App\Models\Product;
use App\Models\PurchaseInvoiceItem;
use App\Models\Unit;
use App\Services\InventoryDispatchService;
use Livewire\Component;

class InventoryDispatchForm extends Component
{
    public ?int $branch_id = null;
    public ?int $delegate_id = null;
    public string $notes = '';
    public string $date = '';
    public array $items = [];

    public function mount()
    {
        $admin = auth('admin')->user();
        if ($admin->branch_id) {
            $this->branch_id = $admin->branch_id;
        }
        $this->date = now()->format('Y-m-d');
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id'          => '',
            'quantity'            => '1',
            'cost_price'          => '0',
            'selling_price'       => '0',
            'base_cost_price'     => '0',
            'base_selling_price'  => '0',
            'unit_id'             => '',
            'unit_symbol'         => '',
            'max_quantity'        => '0',
            'available_units'     => [],
            'stock_unit_id'       => '',
            'stock_unit_factor'   => '1',
        ];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) return;

        $index = (int) $parts[0];
        $field = $parts[1];

        if (!isset($this->items[$index])) return;

        if ($field === 'product_id' && $value) {
            $product = Product::with(['unit', 'branches' => function ($q) {
                $q->where('branch_id', $this->branch_id);
            }])->find($value);

            if ($product && $product->unit) {
                $stockUnit      = $product->unit;
                $stockQty       = (int) ($product->branches->first()?->pivot?->quantity ?? 0);
                $availableUnits = $this->getDispatchableUnits($stockUnit);

                $this->items[$index]['unit_id']           = (string) $stockUnit->id;
                $this->items[$index]['unit_symbol']       = $stockUnit->symbol ?? '';
                $this->items[$index]['max_quantity']      = (string) $stockQty;
                $this->items[$index]['available_units']   = $availableUnits;
                $this->items[$index]['stock_unit_id']     = (string) $stockUnit->id;
                $this->items[$index]['stock_unit_factor'] = (string) $stockUnit->conversion_factor;
                $this->items[$index]['quantity']          = '1';

                // Cost price: last confirmed purchase invoice → fallback to product.cost_price
                $lastItem = PurchaseInvoiceItem::where('product_id', $value)
                    ->whereHas('invoice', fn($q) => $q->whereIn('status', ['confirmed', 'partial_paid', 'paid']))
                    ->latest('id')
                    ->first();

                if ($lastItem) {
                    $unitPrice    = (float) $lastItem->unit_price;
                    $discount     = (float) $lastItem->discount;
                    $discountType = $lastItem->discount_type ?? 'fixed';
                    $costPrice    = $discountType === 'percentage'
                        ? $unitPrice * (1 - $discount / 100)
                        : max(0, $unitPrice - $discount);
                } else {
                    $costPrice = (float) ($product->cost_price ?? 0);
                }

                $this->items[$index]['cost_price']           = (string) round($costPrice, 2);
                $this->items[$index]['selling_price']         = (string) round((float) ($product->selling_price ?? 0), 2);
                $this->items[$index]['base_cost_price']       = (string) round($costPrice, 2);
                $this->items[$index]['base_selling_price']    = (string) round((float) ($product->selling_price ?? 0), 2);
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

    protected function getDispatchableUnits(Unit $stockUnit): array
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
        $selectedUnit   = collect($availableUnits)->firstWhere('id', $selectedUnitId);
        if (!$selectedUnit) return;

        $stockFactor    = (float) ($item['stock_unit_factor'] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);

        $product = Product::with(['branches' => function ($q) {
            $q->where('branch_id', $this->branch_id);
        }])->find($item['product_id'] ?? null);

        $stockQty = (int) ($product?->branches->first()?->pivot?->quantity ?? 0);

        if ($selectedFactor <= 0 || $stockFactor <= 0) return;

        $maxQtyInSelected = (int) floor(($stockQty * $stockFactor) / $selectedFactor);

        $this->items[$index]['unit_symbol']  = $selectedUnit['symbol'] ?? '';
        $this->items[$index]['max_quantity'] = (string) max(0, $maxQtyInSelected);

        // Recalculate prices proportionally: price_per_selected = base_price * (selected_factor / stock_factor)
        $ratio = $selectedFactor / $stockFactor;
        $baseCost    = (float) ($this->items[$index]['base_cost_price']    ?? $this->items[$index]['cost_price']    ?? 0);
        $baseSelling = (float) ($this->items[$index]['base_selling_price'] ?? $this->items[$index]['selling_price'] ?? 0);
        $this->items[$index]['cost_price']    = (string) round($baseCost    * $ratio, 4);
        $this->items[$index]['selling_price'] = (string) round($baseSelling * $ratio, 4);

        $currentQty = (int) ($this->items[$index]['quantity'] ?? 0);
        if ($currentQty > $maxQtyInSelected) {
            $this->items[$index]['quantity'] = (string) max(1, $maxQtyInSelected);
        }
    }

    protected function rules(): array
    {
        return [
            'branch_id'              => 'required|exists:branches,id',
            'delegate_id'            => 'required|exists:delegates,id',
            'date'                   => 'required|date',
            'notes'                  => 'nullable|string|max:500',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.cost_price'     => 'required|numeric|min:0',
            'items.*.selling_price'  => 'required|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'branch_id.required'          => 'الفرع مطلوب',
            'delegate_id.required'        => 'المندوب مطلوب',
            'date.required'               => 'التاريخ مطلوب',
            'items.required'              => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.quantity.required'   => 'الكمية مطلوبة',
        ];
    }

    public function save(InventoryDispatchService $service)
    {
        $this->validate();

        $admin = auth('admin')->user();

        $service->createDispatch([
            'branch_id'   => $this->branch_id,
            'delegate_id' => $this->delegate_id,
            'admin_id'    => $admin->id,
            'date'        => $this->date,
            'notes'       => $this->notes ?: null,
        ], $this->items);

        session()->flash('success', 'تم إنشاء أمر الصرف بنجاح');
        return redirect()->route('inventory-dispatches.index');
    }

    public function render()
    {
        $products = collect();
        if ($this->branch_id) {
            $products = Product::whereHas('branches', function ($q) {
                $q->where('branch_id', $this->branch_id)->where('quantity', '>', 0);
            })->with(['unit', 'branches' => function ($q) {
                $q->where('branch_id', $this->branch_id);
            }])->where('is_active', true)->get();
        }

        return view('livewire.inventory-dispatches.inventory-dispatch-form', [
            'branches'  => Branch::where('is_active', true)->get(),
            'delegates' => Delegate::where('is_active', true)->get(),
            'products'  => $products,
        ]);
    }
}
