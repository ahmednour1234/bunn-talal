<?php

namespace App\Services;

use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use App\Repositories\Contracts\SaleQuotationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SaleQuotationService
{
    public function __construct(protected SaleQuotationRepositoryInterface $quotationRepository)
    {
    }

    public function getById(int $id)
    {
        return $this->quotationRepository->getById($id);
    }

    public function paginateWithFilters(int $perPage, ?string $search, ?string $status, ?int $customerId, ?int $branchId)
    {
        return $this->quotationRepository->paginateWithFilters($perPage, $search, $status, $customerId, $branchId);
    }

    public function createQuotation(array $data, array $items): SaleQuotation
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            $totalTax = 0;

            foreach ($items as &$item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];

                if (!empty($item['discount']) && (float) $item['discount'] > 0) {
                    if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                        $lineTotal -= $lineTotal * ((float) $item['discount'] / 100);
                    } else {
                        $lineTotal -= (float) $item['discount'];
                    }
                }

                $itemTax = !empty($item['tax_amount']) ? (float) $item['tax_amount'] : 0;
                $lineTotal += $itemTax;
                $totalTax += $itemTax;

                $item['total'] = round($lineTotal, 2);
                $subtotal += $item['total'];
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            if ($discountAmount > 0 && ($data['discount_type'] ?? 'fixed') === 'percentage') {
                $discountAmount = $subtotal * ($discountAmount / 100);
            }

            $total = $subtotal - $discountAmount;

            $quotation = $this->quotationRepository->create(array_merge($data, [
                'subtotal'        => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'tax_amount'      => round($totalTax, 2),
                'total'           => round($total, 2),
                'status'          => $data['status'] ?? 'draft',
            ]));

            foreach ($items as $item) {
                $quotation->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'] ?? null,
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'discount'      => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'tax_amount'    => $item['tax_amount'] ?? 0,
                    'total'         => $item['total'],
                ]);
            }

            return $quotation->load(['items.product', 'items.unit', 'customer', 'branch']);
        });
    }

    public function convertToOrder(int $quotationId, array $extraData): SaleOrder
    {
        return DB::transaction(function () use ($quotationId, $extraData) {
            $quotation = $this->quotationRepository->getById($quotationId);

            if ($quotation->status === 'accepted') {
                throw new \Exception('تم تحويل هذا العرض مسبقاً');
            }

            $order = app(SaleOrderService::class)->createOrder(array_merge([
                'sale_quotation_id' => $quotation->id,
                'customer_id'       => $quotation->customer_id,
                'branch_id'         => $quotation->branch_id,
                'admin_id'          => $extraData['admin_id'],
                'delegate_id'       => $quotation->delegate_id,
                'date'              => now()->toDateString(),
                'discount_amount'   => $quotation->discount_amount,
                'discount_type'     => $quotation->discount_type,
                'payment_method'    => $extraData['payment_method'] ?? 'cash',
                'treasury_id'       => $extraData['treasury_id'] ?? null,
                'notes'             => $quotation->notes,
            ], $extraData), $quotation->items->map(fn($i) => [
                'product_id'    => $i->product_id,
                'unit_id'       => $i->unit_id,
                'quantity'      => $i->quantity,
                'unit_price'    => $i->unit_price,
                'discount'      => $i->discount,
                'discount_type' => $i->discount_type,
                'tax_amount'    => $i->tax_amount,
            ])->toArray());

            $quotation->update(['status' => 'accepted']);

            return $order;
        });
    }

    public function cancelQuotation(int $id): SaleQuotation
    {
        $quotation = $this->quotationRepository->getById($id);

        if (in_array($quotation->status, ['accepted', 'rejected'])) {
            throw new \Exception('لا يمكن إلغاء هذا العرض');
        }

        $quotation->update(['status' => 'rejected']);

        return $quotation;
    }
}
