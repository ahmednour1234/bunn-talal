<?php

namespace App\Livewire\SaleQuotations;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Delegate;
use App\Models\Product;
use App\Models\Unit;
use App\Services\SaleQuotationService;
use Livewire\Component;

class SaleQuotationForm extends Component
{
    public ?int $quotationId = null;
    public ?int $customer_id = null;
    public ?int $branch_id = null;
    public ?int $delegate_id = null;
    public string $date = '';
    public string $expiry_date = '';
    public string $discount_amount = '0';
    public string $discount_type = 'fixed';
    public string $notes = '';
    public array $items = [];

    public function mount(?int $id = null)
    {
        $this->date        = now()->format('Y-m-d');
        $this->expiry_date = now()->addDays(30)->format('Y-m-d');

        if ($id) {
            $this->quotationId = $id;
            $quotation = app(SaleQuotationService::class)->getById($id);
            $this->customer_id     = $quotation->customer_id;
            $this->branch_id       = $quotation->branch_id;
            $this->delegate_id     = $quotation->delegate_id;
            $this->date            = $quotation->date->format('Y-m-d');
            $this->expiry_date     = $quotation->expiry_date?->format('Y-m-d') ?? '';
            $this->discount_amount = (string) $quotation->discount_amount;
            $this->discount_type   = $quotation->discount_type;
            $this->notes           = $quotation->notes ?? '';

            foreach ($quotation->items as $item) {
                $this->items[] = [
                    'product_id'        => (string) $item->product_id,
                    'unit_id'           => (string) ($item->unit_id ?? ''),
                    'quantity'          => (string) $item->quantity,
                    'unit_price'        => (string) $item->unit_price,
                    'base_unit_price'   => (string) $item->unit_price,
                    'discount'          => (string) $item->discount,
                    'discount_type'     => $item->discount_type ?? 'fixed',
                    'tax_amount'        => (string) $item->tax_amount,
                    'unit_symbol'       => $item->unit?->symbol ?? '',
                    'available_units'   => [],
                    'stock_unit_factor' => '1',
                ];
            }
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id'        => '',
            'unit_id'           => '',
            'quantity'          => '1',
            'unit_price'        => '0',
            'base_unit_price'   => '0',
            'discount'          => '0',
            'discount_type'     => 'fixed',
            'tax_amount'        => '0',
            'unit_symbol'       => '',
            'available_units'   => [],
            'stock_unit_factor' => '1',
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
            $product = Product::with(['unit', 'tax'])->find($value);
            if ($product && $product->unit) {
                $stockUnit      = $product->unit;
                $availableUnits = $this->getSaleableUnits($stockUnit);
                $sellingPrice   = (float) ($product->selling_price ?? 0);

                $this->items[$index]['unit_id']           = (string) $stockUnit->id;
                $this->items[$index]['unit_symbol']       = $stockUnit->symbol ?? '';
                $this->items[$index]['unit_price']        = (string) round($sellingPrice, 2);
                $this->items[$index]['base_unit_price']   = (string) round($sellingPrice, 2);
                $this->items[$index]['available_units']   = $availableUnits;
                $this->items[$index]['stock_unit_factor'] = (string) $stockUnit->conversion_factor;

                if ($product->tax) {
                    $taxRate = $product->tax->type === 'percentage'
                        ? ($sellingPrice * $product->tax->rate / 100)
                        : $product->tax->rate;
                    $this->items[$index]['tax_amount'] = (string) round($taxRate, 2);
                }
            }
            return;
        }

        if ($field === 'unit_id') {
            $this->applyUnitPriceContext($index);
        }
    }

    protected function getSaleableUnits(Unit $stockUnit): array
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

    protected function applyUnitPriceContext(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (!$item) return;

        $selectedUnitId = (string) ($item['unit_id'] ?? '');
        $availableUnits = $item['available_units'] ?? [];
        $selectedUnit   = collect($availableUnits)->firstWhere('id', $selectedUnitId);
        if (!$selectedUnit) return;

        $stockFactor    = (float) ($item['stock_unit_factor'] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);
        if ($selectedFactor <= 0 || $stockFactor <= 0) return;

        $ratio = $selectedFactor / $stockFactor;
        $base  = (float) ($item['base_unit_price'] ?? $item['unit_price'] ?? 0);

        $this->items[$index]['unit_price']  = (string) round($base * $ratio, 4);
        $this->items[$index]['unit_symbol'] = $selectedUnit['symbol'] ?? '';
    }

    public function getCalculatedTotalsProperty(): array
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $lineTotal = ((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0));
            $discount  = (float) ($item['discount'] ?? 0);
            if ($discount > 0) {
                if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                    $lineTotal -= $lineTotal * ($discount / 100);
                } else {
                    $lineTotal -= $discount;
                }
            }
            $lineTotal += (float) ($item['tax_amount'] ?? 0);
            $subtotal  += $lineTotal;
        }

        $invoiceDiscount = (float) $this->discount_amount;
        if ($invoiceDiscount > 0 && $this->discount_type === 'percentage') {
            $invoiceDiscount = $subtotal * ($invoiceDiscount / 100);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($invoiceDiscount, 2),
            'total'    => round($subtotal - $invoiceDiscount, 2),
        ];
    }

    protected function rules(): array
    {
        return [
            'customer_id'            => 'required|exists:customers,id',
            'branch_id'              => 'required|exists:branches,id',
            'delegate_id'            => 'nullable|exists:delegates,id',
            'date'                   => 'required|date',
            'expiry_date'            => 'nullable|date|after_or_equal:date',
            'discount_amount'        => 'nullable|numeric|min:0',
            'discount_type'          => 'required|in:fixed,percentage',
            'notes'                  => 'nullable|string|max:1000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.unit_id'        => 'nullable|exists:units,id',
            'items.*.quantity'       => 'required|numeric|min:0.0001',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.discount'       => 'nullable|numeric|min:0',
            'items.*.discount_type'  => 'nullable|in:fixed,percentage',
            'items.*.tax_amount'     => 'nullable|numeric|min:0',
        ];
    }

    public function save(SaleQuotationService $service)
    {
        $this->validate();

        $admin = auth('admin')->user();

        $data = [
            'customer_id'    => $this->customer_id,
            'branch_id'      => $this->branch_id,
            'delegate_id'    => $this->delegate_id ?: null,
            'admin_id'       => $admin->id,
            'date'           => $this->date,
            'expiry_date'    => $this->expiry_date ?: null,
            'discount_amount' => $this->discount_amount ?: 0,
            'discount_type'  => $this->discount_type,
            'status'         => 'draft',
            'notes'          => $this->notes ?: null,
        ];

        try {
            $service->createQuotation($data, $this->items);
            session()->flash('success', 'تم إنشاء عرض السعر بنجاح');
            return redirect()->route('sale-quotations.index');
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sale-quotations.sale-quotation-form', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'branches'  => Branch::where('is_active', true)->orderBy('name')->get(),
            'delegates' => Delegate::where('is_active', true)->orderBy('name')->get(),
            'products'  => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
