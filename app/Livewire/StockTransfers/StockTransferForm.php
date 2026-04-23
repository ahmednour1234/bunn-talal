<?php

namespace App\Livewire\StockTransfers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Unit;
use App\Services\StockTransferService;
use Livewire\Component;

class StockTransferForm extends Component
{
    public ?int $from_branch_id = null;
    public ?int $to_branch_id = null;
    public string $notes = '';
    public array $items = [];

    public function mount()
    {
        $admin = auth('admin')->user();
        if ($admin->branch_id) {
            $this->from_branch_id = $admin->branch_id;
        }
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id'      => '',
            'quantity'        => '1',
            'unit_id'         => '',
            'unit_symbol'     => '',
            'max_quantity'    => '0',
            'available_units' => [],
            'stock_unit_id'   => '',
            'stock_unit_factor' => '1',
            'stock_unit_symbol' => '',
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
                $q->where('branch_id', $this->from_branch_id);
            }])->find($value);

            if ($product && $product->unit) {
                $stockUnit = $product->unit;
                $stockQty  = (int) ($product->branches->first()?->pivot?->quantity ?? 0);
                $availableUnits = $this->getTransferableUnits($stockUnit);

                $this->items[$index]['unit_id']           = (string) $stockUnit->id;
                $this->items[$index]['unit_symbol']       = $stockUnit->symbol ?? '';
                $this->items[$index]['max_quantity']      = (string) $stockQty;
                $this->items[$index]['available_units']   = $availableUnits;
                $this->items[$index]['stock_unit_id']     = (string) $stockUnit->id;
                $this->items[$index]['stock_unit_factor'] = (string) $stockUnit->conversion_factor;
                $this->items[$index]['stock_unit_symbol'] = $stockUnit->symbol ?? '';
                $this->items[$index]['quantity']          = '1';
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
            if ($qty < 0) $this->items[$index]['quantity'] = '0';
            elseif ($max > 0 && $qty > $max) $this->items[$index]['quantity'] = (string) $max;
        }
    }

    protected function getTransferableUnits(Unit $stockUnit): array
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

        // Get current stock qty (in stock unit)
        $product = Product::with(['branches' => function ($q) {
            $q->where('branch_id', $this->from_branch_id);
        }])->find($item['product_id'] ?? null);

        $stockQty = (int) ($product?->branches->first()?->pivot?->quantity ?? 0);

        if ($selectedFactor <= 0 || $stockFactor <= 0) return;

        // Max qty in selected unit = floor(stockQty * stockFactor / selectedFactor)
        $maxQtyInSelected = (int) floor(($stockQty * $stockFactor) / $selectedFactor);

        $this->items[$index]['unit_symbol']  = $selectedUnit['symbol'] ?? '';
        $this->items[$index]['max_quantity'] = (string) max(0, $maxQtyInSelected);

        $currentQty = (int) ($this->items[$index]['quantity'] ?? 0);
        if ($currentQty > $maxQtyInSelected) {
            $this->items[$index]['quantity'] = (string) max(0, $maxQtyInSelected);
        }
    }

    protected function rules(): array
    {
        return [
            'from_branch_id'       => 'required|exists:branches,id',
            'to_branch_id'         => 'required|exists:branches,id|different:from_branch_id',
            'notes'                => 'nullable|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_id'      => 'nullable|exists:units,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'from_branch_id.required'    => 'فرع المصدر مطلوب',
            'to_branch_id.required'      => 'فرع الوجهة مطلوب',
            'to_branch_id.different'     => 'فرع الوجهة يجب أن يختلف عن المصدر',
            'items.required'             => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required'=> 'يجب اختيار المنتج',
            'items.*.quantity.required'  => 'الكمية مطلوبة',
            'items.*.quantity.min'       => 'الكمية يجب أن تكون 1 على الأقل',
        ];
    }

    public function save(StockTransferService $service)
    {
        $this->validate();

        $admin = auth('admin')->user();

        $service->createTransfer([
            'from_branch_id' => $this->from_branch_id,
            'to_branch_id'   => $this->to_branch_id,
            'requested_by'   => $admin->id,
            'status'         => 'pending',
            'notes'          => $this->notes ?: null,
        ], $this->items);

        session()->flash('success', 'تم إنشاء طلب التحويل بنجاح');
        return redirect()->route('stock-transfers.index');
    }

    public function render()
    {
        $products = collect();
        if ($this->from_branch_id) {
            $products = Product::whereHas('branches', function ($q) {
                $q->where('branch_id', $this->from_branch_id)->where('quantity', '>', 0);
            })->with(['unit', 'branches' => function ($q) {
                $q->where('branch_id', $this->from_branch_id);
            }])->where('is_active', true)->get();
        }

        return view('livewire.stock-transfers.stock-transfer-form', [
            'branches' => Branch::where('is_active', true)->get(),
            'products' => $products,
        ]);
    }
}
