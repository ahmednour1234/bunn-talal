<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Models\Unit;
use App\Repositories\Contracts\PurchaseReturnRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(protected PurchaseReturnRepositoryInterface $returnRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->returnRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId, ?int $branchId = null)
    {
        return $this->returnRepository->paginateWithFilters($perPage, $search, $status, $supplierId, $branchId);
    }

    public function getFilteredReturns(?string $search, ?string $status, ?int $supplierId, ?int $branchId = null)
    {
        return $this->buildFilteredQuery($search, $status, $supplierId, $branchId)
            ->with(['invoice', 'supplier', 'branch', 'admin'])
            ->latest()
            ->get();
    }

    public function getSummaryStats(?string $search, ?string $status, ?int $supplierId, ?int $branchId = null): array
    {
        $baseQuery = $this->buildFilteredQuery($search, $status, $supplierId, $branchId);

        $totals = (clone $baseQuery)->selectRaw('COUNT(*) as count, COALESCE(SUM(subtotal),0) as subtotal, COALESCE(SUM(loss_amount),0) as loss, COALESCE(SUM(refund_amount),0) as refund')->first();

        $statusCounts = (clone $this->buildFilteredQuery($search, null, $supplierId, $branchId))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'count' => (int) ($totals->count ?? 0),
            'subtotal' => (float) ($totals->subtotal ?? 0),
            'loss' => (float) ($totals->loss ?? 0),
            'refund' => (float) ($totals->refund ?? 0),
            'status_counts' => [
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'confirmed' => (int) ($statusCounts['confirmed'] ?? 0),
                'refunded' => (int) ($statusCounts['refunded'] ?? 0),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            ],
        ];
    }

    protected function buildFilteredQuery(?string $search, ?string $status, ?int $supplierId, ?int $branchId = null)
    {
        $query = PurchaseReturn::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('invoice', fn($i) => $i->where('invoice_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    public function createReturn(array $data, array $items): PurchaseReturn
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            $totalLoss = 0;

            foreach ($items as &$item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                $totalLoss += (float) ($item['loss_amount'] ?? 0);
            }

            $refundAmount = $subtotal - $totalLoss;

            $return = $this->returnRepository->create(array_merge($data, [
                'subtotal' => round($subtotal, 2),
                'loss_amount' => round($totalLoss, 2),
                'refund_amount' => round(max($refundAmount, 0), 2),
                'status' => 'pending',
            ]));

            foreach ($items as $item) {
                $return->items()->create([
                    'purchase_invoice_item_id' => $item['purchase_invoice_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'loss_amount' => $item['loss_amount'] ?? 0,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            return $return->load(['items.product', 'items.unit', 'invoice', 'supplier']);
        });
    }

    public function confirmReturn(int $id): PurchaseReturn
    {
        return DB::transaction(function () use ($id) {
            $return = $this->returnRepository->getById($id);

            if ($return->status !== 'pending') {
                throw new \Exception('لا يمكن تأكيد هذا المرتجع');
            }

            // ── Validate stock availability before any deduction ──────────
            foreach ($return->items as $item) {
                $current = DB::table('branch_product')
                    ->where('branch_id', $return->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$current) {
                    throw new \Exception("المنتج [{$item->product->name}] غير موجود في الفرع");
                }

                $returnUnit = $item->unit_id ? Unit::find($item->unit_id) : null;
                $stockUnit  = $current->unit_id ? Unit::find($current->unit_id) : null;

                if (!$returnUnit || !$stockUnit) {
                    if ((float) $current->quantity < (float) $item->quantity) {
                        throw new \Exception("الكمية المتاحة في الفرع غير كافية للمنتج [{$item->product->name}]");
                    }
                    continue;
                }

                $qtyInStockUnit = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $stockUnit->conversion_factor;

                if (abs($qtyInStockUnit - round($qtyInStockUnit)) < 0.000001) {
                    $deductQty = (int) round($qtyInStockUnit);
                    if ((int) $current->quantity < $deductQty) {
                        throw new \Exception("الكمية المتاحة في الفرع غير كافية للمنتج [{$item->product->name}]");
                    }
                    continue;
                }

                $baseUnitId = $this->resolveBaseUnitId($stockUnit);
                $baseUnit   = Unit::find($baseUnitId);
                if (!$baseUnit) {
                    continue;
                }

                $currentQtyInBase = (int) round(((float) $current->quantity * (float) $stockUnit->conversion_factor) / (float) $baseUnit->conversion_factor);
                $returnQtyInBase  = (int) round(((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $baseUnit->conversion_factor);

                if ($currentQtyInBase < $returnQtyInBase) {
                    throw new \Exception("الكمية المتاحة في الفرع غير كافية للمنتج [{$item->product->name}]");
                }
            }

            // ── Deduct stock from branch ──────────────────────────────────
            foreach ($return->items as $item) {
                $current = DB::table('branch_product')
                    ->where('branch_id', $return->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$current) {
                    continue;
                }

                $returnUnit = $item->unit_id ? Unit::find($item->unit_id) : null;
                $stockUnit  = $current->unit_id ? Unit::find($current->unit_id) : null;

                if (!$returnUnit || !$stockUnit) {
                    DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('quantity', $item->quantity);
                    continue;
                }

                $qtyInStockUnit = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $stockUnit->conversion_factor;

                if (abs($qtyInStockUnit - round($qtyInStockUnit)) < 0.000001) {
                    DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('quantity', (int) round($qtyInStockUnit));
                    continue;
                }

                $baseUnitId = $this->resolveBaseUnitId($stockUnit);
                $baseUnit   = Unit::find($baseUnitId);
                if (!$baseUnit) {
                    continue;
                }

                $currentQtyInBase = (int) round(((float) $current->quantity * (float) $stockUnit->conversion_factor) / (float) $baseUnit->conversion_factor);
                $returnQtyInBase  = (int) round(((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $baseUnit->conversion_factor);

                DB::table('branch_product')
                    ->where('id', $current->id)
                    ->update([
                        'unit_id'    => $baseUnit->id,
                        'quantity'   => $currentQtyInBase - $returnQtyInBase,
                        'updated_at' => now(),
                    ]);
            }

            // ── Reduce supplier balance by refund amount ──────────────────
            Supplier::where('id', $return->supplier_id)->decrement('balance', $return->refund_amount);

            // ── Update purchase invoice paid_amount and status ────────────
            if ($return->purchase_invoice_id && $return->refund_amount > 0) {
                $invoice = PurchaseInvoice::find($return->purchase_invoice_id);
                if ($invoice && !in_array($invoice->status, ['paid', 'cancelled'])) {
                    $invoice->increment('paid_amount', $return->refund_amount);
                    $invoice->refresh();
                    $newPaid = (float) $invoice->paid_amount;
                    $total   = (float) $invoice->total;
                    if ($newPaid >= $total) {
                        $invoice->update(['status' => 'paid']);
                    } elseif ($newPaid > 0) {
                        $invoice->update(['status' => 'partial_paid']);
                    }
                }
            }

            // ── If treasury specified, deposit refund INTO treasury ────────
            if ($return->treasury_id && $return->refund_amount > 0) {
                Treasury::where('id', $return->treasury_id)->increment('balance', $return->refund_amount);
                TreasuryTransaction::create([
                    'treasury_id'      => $return->treasury_id,
                    'type'             => 'deposit',
                    'amount'           => $return->refund_amount,
                    'description'      => 'مرتجع مشتريات #' . $return->return_number,
                    'reference_number' => $return->return_number,
                    'date'             => now()->toDateString(),
                    'admin_id'         => auth('admin')->id(),
                ]);
                $return->update(['status' => 'refunded']);
            } else {
                $return->update(['status' => 'confirmed']);
            }

            return $return;
        });
    }

    public function cancelReturn(int $id): PurchaseReturn
    {
        $return = $this->returnRepository->getById($id);

        if ($return->status !== 'pending') {
            throw new \Exception('لا يمكن إلغاء هذا المرتجع');
        }

        $return->update(['status' => 'cancelled']);
        return $return;
    }

    public function reverseReturn(int $id): PurchaseReturn
    {
        return DB::transaction(function () use ($id) {
            $return = $this->returnRepository->getById($id);

            if (!in_array($return->status, ['confirmed', 'refunded'])) {
                throw new \Exception('لا يمكن عكس هذا المرتجع — الحالة يجب أن تكون مؤكد أو مستردّ');
            }

            // ── Restore stock to branch ───────────────────────────────────
            foreach ($return->items as $item) {
                $current = DB::table('branch_product')
                    ->where('branch_id', $return->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $returnUnit = $item->unit_id ? Unit::find($item->unit_id) : null;

                if (!$current) {
                    DB::table('branch_product')->insert([
                        'branch_id'  => $return->branch_id,
                        'product_id' => $item->product_id,
                        'quantity'   => (float) $item->quantity,
                        'unit_id'    => $item->unit_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    continue;
                }

                $stockUnit = $current->unit_id ? Unit::find($current->unit_id) : null;

                if (!$returnUnit || !$stockUnit) {
                    DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', (float) $item->quantity);
                    continue;
                }

                $qtyInStockUnit = ((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $stockUnit->conversion_factor;

                if (abs($qtyInStockUnit - round($qtyInStockUnit)) < 0.000001) {
                    DB::table('branch_product')
                        ->where('branch_id', $return->branch_id)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', (int) round($qtyInStockUnit));
                    continue;
                }

                $baseUnitId = $this->resolveBaseUnitId($stockUnit);
                $baseUnit   = Unit::find($baseUnitId);
                if (!$baseUnit) {
                    continue;
                }

                $currentQtyInBase = (int) round(((float) $current->quantity * (float) $stockUnit->conversion_factor) / (float) $baseUnit->conversion_factor);
                $returnQtyInBase  = (int) round(((float) $item->quantity * (float) $returnUnit->conversion_factor) / (float) $baseUnit->conversion_factor);

                DB::table('branch_product')
                    ->where('id', $current->id)
                    ->update([
                        'unit_id'    => $baseUnit->id,
                        'quantity'   => $currentQtyInBase + $returnQtyInBase,
                        'updated_at' => now(),
                    ]);
            }

            // ── Restore supplier balance ──────────────────────────────────
            Supplier::where('id', $return->supplier_id)->increment('balance', $return->refund_amount);

            // ── Reverse invoice paid_amount and status ────────────────────
            if ($return->purchase_invoice_id && $return->refund_amount > 0) {
                $invoice = PurchaseInvoice::find($return->purchase_invoice_id);
                if ($invoice && $invoice->status !== 'cancelled') {
                    $newPaid = max(0, (float) $invoice->paid_amount - (float) $return->refund_amount);
                    $total   = (float) $invoice->total;
                    $status  = $newPaid >= $total ? 'paid' : ($newPaid > 0 ? 'partial_paid' : 'confirmed');
                    $invoice->update(['paid_amount' => $newPaid, 'status' => $status]);
                }
            }

            // ── Reverse treasury deposit ──────────────────────────────────
            if ($return->treasury_id && $return->refund_amount > 0) {
                Treasury::where('id', $return->treasury_id)->decrement('balance', $return->refund_amount);
                TreasuryTransaction::create([
                    'treasury_id'      => $return->treasury_id,
                    'type'             => 'withdrawal',
                    'amount'           => $return->refund_amount,
                    'description'      => 'عكس مرتجع مشتريات #' . $return->return_number,
                    'reference_number' => $return->return_number,
                    'date'             => now()->toDateString(),
                    'admin_id'         => auth('admin')->id(),
                ]);
            }

            $return->update(['status' => 'cancelled']);
            return $return;
        });
    }

    protected function resolveBaseUnitId(Unit $unit): int
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
}
