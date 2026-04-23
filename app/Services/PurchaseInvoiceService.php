<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\Treasury;
use App\Models\Supplier;
use App\Repositories\Contracts\PurchaseInvoiceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceService
{
    public function __construct(protected PurchaseInvoiceRepositoryInterface $invoiceRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->invoiceRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $supplierId, ?int $branchId)
    {
        return $this->invoiceRepository->paginateWithFilters($perPage, $search, $status, $supplierId, $branchId);
    }

    public function getFilteredInvoices(?string $search, ?string $status, ?int $supplierId, ?int $branchId)
    {
        return $this->buildFilteredQuery($search, $status, $supplierId, $branchId)
            ->latest()
            ->get();
    }

    public function getStatusSummary(?string $search, ?string $status, ?int $supplierId, ?int $branchId): array
    {
        $rows = $this->buildFilteredQuery($search, null, $supplierId, $branchId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $result = [];
        foreach (PurchaseInvoice::statusLabels() as $key => $label) {
            if ($status && $status !== $key) {
                $result[$key] = 0;
                continue;
            }
            $result[$key] = (int) ($rows[$key] ?? 0);
        }

        return $result;
    }

    protected function buildFilteredQuery(?string $search, ?string $status, ?int $supplierId, ?int $branchId)
    {
        $query = PurchaseInvoice::query()->with(['supplier', 'branch', 'admin', 'treasury']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
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

    public function createInvoice(array $data, array $items, ?float $initialPayment = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($data, $items, $initialPayment) {
            $subtotal = 0;
            $totalTax = 0;

            // Calculate item totals
            foreach ($items as &$item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];

                // Apply item discount
                if (!empty($item['discount']) && $item['discount'] > 0) {
                    if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                        $lineTotal -= $lineTotal * ($item['discount'] / 100);
                    } else {
                        $lineTotal -= $item['discount'];
                    }
                }

                $itemTax = !empty($item['tax_amount']) ? (float) $item['tax_amount'] : 0;
                $lineTotal += $itemTax;
                $totalTax += $itemTax;

                $item['total'] = round($lineTotal, 2);
                $subtotal += $item['total'];
            }

            // Calculate invoice-level discount
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            if ($discountAmount > 0 && ($data['discount_type'] ?? 'fixed') === 'percentage') {
                $discountAmount = $subtotal * ($discountAmount / 100);
            }

            $total = $subtotal - $discountAmount;

            $invoice = $this->invoiceRepository->create(array_merge($data, [
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'tax_amount' => round($totalTax, 2),
                'total' => round($total, 2),
                'paid_amount' => 0,
                'status' => 'confirmed',
            ]));

            // Create items and add stock
            foreach ($items as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                ]);

                // Add stock to branch (works for both existing and first-time product rows)
                $stockRow = DB::table('branch_product')
                    ->where('branch_id', $data['branch_id'])
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($stockRow) {
                    DB::table('branch_product')
                        ->where('id', $stockRow->id)
                        ->update([
                            'quantity' => ((int) $stockRow->quantity) + ((int) $item['quantity']),
                            'unit_id' => $item['unit_id'] ?? $stockRow->unit_id,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('branch_product')->insert([
                        'branch_id' => $data['branch_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => (int) $item['quantity'],
                        'unit_id' => $item['unit_id'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Update supplier balance
            Supplier::where('id', $data['supplier_id'])->increment('balance', $total);

            // Process initial payment if any
            if ($initialPayment && $initialPayment > 0) {
                $this->processPayment($invoice, $initialPayment, $data['treasury_id'] ?? null, $data['admin_id']);
            } elseif (($data['payment_method'] ?? 'cash') === 'cash') {
                // Full cash payment
                $this->processPayment($invoice, $total, $data['treasury_id'] ?? null, $data['admin_id']);
            }

            return $invoice->load(['items.product', 'items.unit', 'supplier', 'branch']);
        });
    }

    protected function processPayment(PurchaseInvoice $invoice, float $amount, ?int $treasuryId, int $adminId): void
    {
        if ($amount <= 0) return;

        $remaining = (float) $invoice->total - (float) $invoice->paid_amount;
        $payAmount = min($amount, $remaining);

        $invoice->payments()->create([
            'amount' => $payAmount,
            'payment_date' => now()->toDateString(),
            'treasury_id' => $treasuryId,
            'payment_method' => $treasuryId ? 'cash' : 'credit',
            'admin_id' => $adminId,
        ]);

        // Deduct from treasury
        if ($treasuryId) {
            Treasury::where('id', $treasuryId)->decrement('balance', $payAmount);
        }

        // Update invoice
        $newPaid = (float) $invoice->paid_amount + $payAmount;
        $invoice->update([
            'paid_amount' => $newPaid,
            'status' => $newPaid >= (float) $invoice->total ? 'paid' : 'partial_paid',
        ]);

        // Reduce supplier balance
        Supplier::where('id', $invoice->supplier_id)->decrement('balance', $payAmount);
    }

    public function addPayment(int $invoiceId, array $paymentData): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoiceId, $paymentData) {
            $invoice = $this->invoiceRepository->getById($invoiceId);

            if (in_array($invoice->status, ['cancelled', 'paid'])) {
                throw new \Exception('لا يمكن إضافة دفعة لهذه الفاتورة');
            }

            $this->processPayment(
                $invoice,
                (float) $paymentData['amount'],
                $paymentData['treasury_id'] ?? null,
                $paymentData['admin_id']
            );

            return $invoice->fresh(['payments', 'supplier']);
        });
    }

    public function cancelInvoice(int $id): PurchaseInvoice
    {
        return DB::transaction(function () use ($id) {
            $invoice = $this->invoiceRepository->getById($id);

            if ($invoice->status === 'cancelled') {
                throw new \Exception('الفاتورة ملغاة بالفعل');
            }

            // Reverse stock additions
            foreach ($invoice->items as $item) {
                DB::table('branch_product')
                    ->where('branch_id', $invoice->branch_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('quantity', $item->quantity);
            }

            // Reverse supplier balance (total - paid)
            $unpaid = (float) $invoice->total - (float) $invoice->paid_amount;
            Supplier::where('id', $invoice->supplier_id)->decrement('balance', $unpaid);

            // Refund paid amount to treasury
            foreach ($invoice->payments as $payment) {
                if ($payment->treasury_id) {
                    Treasury::where('id', $payment->treasury_id)->increment('balance', $payment->amount);
                }
            }

            // Restore full supplier balance deduction
            Supplier::where('id', $invoice->supplier_id)->increment('balance', (float) $invoice->paid_amount);
            Supplier::where('id', $invoice->supplier_id)->decrement('balance', (float) $invoice->total);

            $invoice->update(['status' => 'cancelled']);

            return $invoice;
        });
    }
}
