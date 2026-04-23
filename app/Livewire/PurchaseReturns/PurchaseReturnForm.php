<?php

namespace App\Livewire\PurchaseReturns;

use App\Models\PurchaseInvoice;
use App\Models\Treasury;
use App\Models\Unit;
use App\Services\PurchaseReturnService;
use Livewire\Component;

class PurchaseReturnForm extends Component
{
    public ?int $purchase_invoice_id = null;
    public ?int $treasury_id = null;
    public string $date = '';
    public string $notes = '';
    public array $items = [];

    public ?int $loaded_supplier_id = null;
    public ?int $loaded_branch_id = null;
    public ?string $loaded_supplier_name = null;
    public ?string $loaded_invoice_number = null;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedPurchaseInvoiceId($value)
    {
        $this->items = [];
        $this->loaded_supplier_id = null;
        $this->loaded_branch_id = null;
        $this->loaded_supplier_name = null;
        $this->loaded_invoice_number = null;

        if ($value) {
            $invoice = PurchaseInvoice::with(['items.product', 'items.unit.baseUnit', 'supplier'])->find($value);
            if ($invoice) {
                $this->loaded_supplier_id = $invoice->supplier_id;
                $this->loaded_branch_id = $invoice->branch_id;
                $this->loaded_supplier_name = $invoice->supplier->name;
                $this->loaded_invoice_number = $invoice->invoice_number;

                foreach ($invoice->items as $item) {
                    $invoiceUnit = $item->unit ?: $item->product?->unit;
                    if (!$invoiceUnit) {
                        continue;
                    }

                    $availableUnits = $this->getReturnableUnits($invoiceUnit);

                    $this->items[] = [
                        'purchase_invoice_item_id' => (string) $item->id,
                        'product_id' => (string) $item->product_id,
                        'product_name' => $item->product->name,
                        'unit_id' => (string) $invoiceUnit->id,
                        'unit_symbol' => $invoiceUnit->symbol ?? '',
                        'invoice_unit_symbol' => $invoiceUnit->symbol ?? '',
                        'original_qty' => (string) $item->quantity,
                        'unit_price' => (string) $item->unit_price,
                        'invoice_unit_id' => (string) $invoiceUnit->id,
                        'invoice_unit_factor' => (string) $invoiceUnit->conversion_factor,
                        'invoice_unit_price' => (string) $item->unit_price,
                        'max_quantity' => (string) $item->quantity,
                        'available_units' => $availableUnits,
                        'quantity' => '0',
                        'loss_amount' => '0',
                        'reason' => '',
                    ];
                }
            }
        }
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
            $max = (int) ($this->items[$index]['max_quantity'] ?? 0);
            $qty = (int) ($this->items[$index]['quantity'] ?? 0);

            if ($qty < 0) {
                $this->items[$index]['quantity'] = '0';
            } elseif ($max > 0 && $qty > $max) {
                $this->items[$index]['quantity'] = (string) $max;
            }
        }
    }

    protected function getReturnableUnits(Unit $invoiceUnit): array
    {
        $rootId = $this->getRootUnitId($invoiceUnit);

        return Unit::query()
            ->where('is_active', true)
            ->where(function ($q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('base_unit_id', $rootId);
            })
            ->where('conversion_factor', '<=', $invoiceUnit->conversion_factor)
            ->orderBy('conversion_factor', 'desc')
            ->get(['id', 'name', 'symbol', 'conversion_factor'])
            ->map(fn($u) => [
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
        while ($current->base_unit_id) {
            $parent = Unit::find($current->base_unit_id);
            if (!$parent) {
                break;
            }
            $current = $parent;
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
        $availableUnits = $item['available_units'] ?? [];
        $selectedUnit = collect($availableUnits)->firstWhere('id', $selectedUnitId);

        if (!$selectedUnit) {
            return;
        }

        $invoiceFactor = (float) ($item['invoice_unit_factor'] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);
        $invoiceUnitPrice = (float) ($item['invoice_unit_price'] ?? 0);
        $originalQty = (float) ($item['original_qty'] ?? 0);

        if ($invoiceFactor <= 0 || $selectedFactor <= 0) {
            return;
        }

        $maxQtyInSelected = (int) floor(($originalQty * $invoiceFactor) / $selectedFactor);
        $unitPriceInSelected = $invoiceUnitPrice * ($selectedFactor / $invoiceFactor);

        $this->items[$index]['unit_symbol'] = $selectedUnit['symbol'] ?? '';
        $this->items[$index]['max_quantity'] = (string) max(0, $maxQtyInSelected);
        $this->items[$index]['unit_price'] = (string) round($unitPriceInSelected, 6);

        $currentQty = (int) ($this->items[$index]['quantity'] ?? 0);
        if ($currentQty > $maxQtyInSelected) {
            $this->items[$index]['quantity'] = (string) max(0, $maxQtyInSelected);
        }
    }

    public function getCalculatedTotalsProperty(): array
    {
        $subtotal = 0;
        $totalLoss = 0;
        foreach ($this->items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $loss = (float) ($item['loss_amount'] ?? 0);
            $subtotal += $qty * $price;
            $totalLoss += $loss;
        }
        return [
            'subtotal' => round($subtotal, 2),
            'loss' => round($totalLoss, 2),
            'refund' => round(max($subtotal - $totalLoss, 0), 2),
        ];
    }

    protected function rules(): array
    {
        return [
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'date' => 'required|date',
            'treasury_id' => 'nullable|exists:treasuries,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.loss_amount' => 'nullable|numeric|min:0',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'purchase_invoice_id.required' => 'يجب اختيار فاتورة المشتريات',
            'items.required' => 'يجب تحديد المنتجات المرتجعة',
        ];
    }

    public function save(PurchaseReturnService $service)
    {
        $this->validate();

        // Filter out zero-quantity items
        $returnItems = collect($this->items)->filter(fn($item) => ((int) ($item['quantity'] ?? 0)) > 0)->values()->toArray();

        if (empty($returnItems)) {
            session()->flash('error', 'يجب إدخال كمية مرتجعة لمنتج واحد على الأقل');
            return;
        }

        foreach ($returnItems as $returnItem) {
            $qty = (int) ($returnItem['quantity'] ?? 0);
            $maxQty = (int) ($returnItem['max_quantity'] ?? 0);
            if ($maxQty > 0 && $qty > $maxQty) {
                session()->flash('error', 'كمية الإرجاع تتجاوز الحد المسموح للوحدة المختارة');
                return;
            }
        }

        $admin = auth('admin')->user();

        try {
            $service->createReturn([
                'purchase_invoice_id' => $this->purchase_invoice_id,
                'supplier_id' => $this->loaded_supplier_id,
                'branch_id' => $this->loaded_branch_id,
                'admin_id' => $admin->id,
                'date' => $this->date,
                'treasury_id' => $this->treasury_id,
                'notes' => $this->notes ?: null,
            ], $returnItems);

            session()->flash('success', 'تم إنشاء مرتجع المشتريات بنجاح');
            return redirect()->route('purchase-returns.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $invoices = PurchaseInvoice::whereNotIn('status', ['cancelled', 'draft'])
            ->with('supplier')
            ->orderByDesc('date')
            ->get();

        return view('livewire.purchase-returns.purchase-return-form', [
            'invoices' => $invoices,
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
