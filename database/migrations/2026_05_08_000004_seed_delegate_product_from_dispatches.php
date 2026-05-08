<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Calculate current delegate stock per product from historical data:
        //   dispatched - sold (non-cancelled) + sale_returns (confirmed/refunded) = current stock

        // Step 1: Sum dispatched quantities per delegate + product
        $dispatched = DB::table('inventory_dispatch_items as idi')
            ->join('inventory_dispatches as id', 'id.id', '=', 'idi.inventory_dispatch_id')
            ->whereNotNull('id.delegate_id')
            ->whereNull('id.deleted_at')
            ->select(
                'id.delegate_id',
                'idi.product_id',
                DB::raw('SUM(idi.quantity) as total_dispatched')
            )
            ->groupBy('id.delegate_id', 'idi.product_id')
            ->get()
            ->keyBy(fn($r) => "{$r->delegate_id}_{$r->product_id}");

        // Step 2: Sum sold quantities per delegate + product (exclude cancelled orders)
        $sold = DB::table('sale_order_items as soi')
            ->join('sale_orders as so', 'so.id', '=', 'soi.sale_order_id')
            ->whereNotNull('so.delegate_id')
            ->where('so.status', '!=', 'cancelled')
            ->whereNull('so.deleted_at')
            ->select(
                'so.delegate_id',
                'soi.product_id',
                DB::raw('SUM(soi.quantity) as total_sold')
            )
            ->groupBy('so.delegate_id', 'soi.product_id')
            ->get()
            ->keyBy(fn($r) => "{$r->delegate_id}_{$r->product_id}");

        // Step 3: Sum returned quantities per delegate + product (confirmed/refunded returns)
        $returned = DB::table('sale_return_items as sri')
            ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
            ->join('sale_orders as so', 'so.id', '=', 'sr.sale_order_id')
            ->whereNotNull('so.delegate_id')
            ->whereIn('sr.status', ['confirmed', 'refunded'])
            ->whereNull('sr.deleted_at')
            ->select(
                'so.delegate_id',
                'sri.product_id',
                DB::raw('SUM(sri.quantity) as total_returned')
            )
            ->groupBy('so.delegate_id', 'sri.product_id')
            ->get()
            ->keyBy(fn($r) => "{$r->delegate_id}_{$r->product_id}");

        // Step 4: Build and insert/update delegate_product rows
        $now = now();
        foreach ($dispatched as $key => $row) {
            $totalSold     = (float) ($sold[$key]?->total_sold ?? 0);
            $totalReturned = (float) ($returned[$key]?->total_returned ?? 0);
            $remaining     = max(0, (float) $row->total_dispatched - $totalSold + $totalReturned);

            if ($remaining <= 0) {
                continue;
            }

            DB::table('delegate_product')->upsert(
                [
                    'delegate_id' => $row->delegate_id,
                    'product_id'  => $row->product_id,
                    'quantity'    => $remaining,
                    'unit_id'     => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                ['delegate_id', 'product_id'],
                ['quantity', 'updated_at']
            );
        }
    }

    public function down(): void
    {
        DB::table('delegate_product')->truncate();
    }
};
