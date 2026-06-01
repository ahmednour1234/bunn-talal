<?php

namespace App\Livewire\InventoryDispatches;

use App\Models\Treasury;
use App\Models\Unit;
use App\Services\InventoryDispatchService;
use Livewire\Component;

class InventoryDispatchShow extends Component
{
    public int $dispatchId;
    public array $returnQuantities    = [];
    public array $returnUnitIds       = [];
    public array $returnAvailableUnits = [];
    public array $returnMaxQuantities = [];
    public array $returnStockFactors  = [];
    public array $returnUnitSymbols   = [];
    public string $actualSales = '';
    public ?int $settleTreasuryId = null;
    public bool $showReturnForm = false;
    public bool $showSettleForm = false;

    public function mount(int $id, InventoryDispatchService $service)
    {
        $this->dispatchId = $id;
        $dispatch = $service->getById($id);

        foreach ($dispatch->items as $item) {
            $remaining  = $item->quantity - ($item->returned_quantity ?? 0);
            $stockUnit  = $item->product?->unit;
            $units      = $stockUnit ? $this->getReturnableUnits($stockUnit) : [];

            $this->returnQuantities[$item->id]     = 0;
            $this->returnUnitIds[$item->id]        = $stockUnit ? (string) $stockUnit->id : '';
            $this->returnAvailableUnits[$item->id] = $units;
            $this->returnMaxQuantities[$item->id]  = (string) $remaining;
            $this->returnStockFactors[$item->id]   = $stockUnit ? (string) $stockUnit->conversion_factor : '1';
            $this->returnUnitSymbols[$item->id]    = $stockUnit?->symbol ?? '';
        }
    }

    public function updatedReturnUnitIds($value, $key)
    {
        // $key is the item id
        $itemId = $key;
        $units  = $this->returnAvailableUnits[$itemId] ?? [];
        $selectedUnit = collect($units)->firstWhere('id', (string) $value);
        if (!$selectedUnit) return;

        $stockFactor    = (float) ($this->returnStockFactors[$itemId] ?? 1);
        $selectedFactor = (float) ($selectedUnit['factor'] ?? 1);

        // We need the remaining in stock unit — reload from DB
        $dispatch = app(InventoryDispatchService::class)->getById($this->dispatchId);
        $item = $dispatch->items->firstWhere('id', (int) $itemId);
        $remaining = $item ? ($item->quantity - ($item->returned_quantity ?? 0)) : 0;

        if ($selectedFactor <= 0 || $stockFactor <= 0) return;

        $max = (int) floor(($remaining * $stockFactor) / $selectedFactor);
        $this->returnMaxQuantities[$itemId] = (string) max(0, $max);
        $this->returnUnitSymbols[$itemId]   = $selectedUnit['symbol'] ?? '';

        if ((int) ($this->returnQuantities[$itemId] ?? 0) > $max) {
            $this->returnQuantities[$itemId] = max(0, $max);
        }
    }

    public function toggleReturnForm()
    {
        $this->showReturnForm = !$this->showReturnForm;
        $this->showSettleForm = false;
    }

    public function toggleSettleForm()
    {
        $this->showSettleForm = !$this->showSettleForm;
        $this->showReturnForm = false;
    }

    public function submitReturn(InventoryDispatchService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('inventory-dispatches.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        // Convert each qty to stock unit before saving
        $convertedQtys = [];
        foreach ($this->returnQuantities as $itemId => $qty) {
            if ((int) $qty <= 0) {
                $convertedQtys[$itemId] = 0;
                continue;
            }

            $units        = $this->returnAvailableUnits[$itemId] ?? [];
            $selectedUnit = collect($units)->firstWhere('id', (string) ($this->returnUnitIds[$itemId] ?? ''));
            $stockFactor  = (float) ($this->returnStockFactors[$itemId] ?? 1);
            $selFactor    = $selectedUnit ? (float) $selectedUnit['factor'] : $stockFactor;

            if ($selFactor <= 0 || $stockFactor <= 0) {
                $convertedQtys[$itemId] = (int) $qty;
                continue;
            }

            $inStock = ($qty * $selFactor) / $stockFactor;
            $convertedQtys[$itemId] = (int) round($inStock);
        }

        try {
            $service->returnItems($this->dispatchId, $convertedQtys);
            session()->flash('success', 'تم تسجيل المرتجعات بنجاح');
            $this->showReturnForm = false;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function submitSettle(InventoryDispatchService $service)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('inventory-dispatches.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $this->validate([
            'actualSales'      => 'required|numeric|min:0',
            'settleTreasuryId' => 'nullable|exists:treasuries,id',
        ], [
            'actualSales.required' => 'المبيعات الفعلية مطلوبة',
        ]);

        $admin = auth('admin')->user();

        try {
            $service->settleDispatch(
                $this->dispatchId,
                (float) $this->actualSales,
                $this->settleTreasuryId,
                $admin->id
            );
            session()->flash('success', 'تمت تسوية أمر الصرف بنجاح');
            $this->showSettleForm = false;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    protected function getReturnableUnits(Unit $stockUnit): array
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

    public function cancelDispatch(InventoryDispatchService $service): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('inventory-dispatches.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $dispatch = $service->getById($this->dispatchId);

        if ($dispatch->status !== 'pending') return;

        $dispatch->update(['status' => 'cancelled']);

        session()->flash('success', 'تم رفض أمر الصرف');
    }

    public function render(InventoryDispatchService $service)
    {
        return view('livewire.inventory-dispatches.inventory-dispatch-show', [
            'dispatch'   => $service->getById($this->dispatchId),
            'treasuries' => Treasury::where('is_active', true)->get(['id', 'name']),
        ]);
    }
}
