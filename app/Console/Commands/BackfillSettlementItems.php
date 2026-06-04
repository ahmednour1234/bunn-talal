<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('trips:backfill-settlement-items {--trip= : Specific trip ID to backfill}')]
#[Description('Backfill settlement_items snapshot for settled trips that were settled before this feature was added')]
class BackfillSettlementItems extends Command
{
    public function handle(): int
    {
        $query = Trip::query()
            ->whereIn('status', ['settled'])
            ->whereNull('settlement_items')
            ->with([
                'dispatches.items',
                'saleOrders.items',
                'saleReturns.items',
            ]);

        if ($tripId = $this->option('trip')) {
            $query->where('id', $tripId);
        }

        $trips = $query->get();

        if ($trips->isEmpty()) {
            $this->info('No trips need backfilling.');
            return self::SUCCESS;
        }

        $this->info("Found {$trips->count()} trip(s) to backfill.");
        $bar = $this->output->createProgressBar($trips->count());
        $bar->start();

        foreach ($trips as $trip) {
            // ── 1. Sum dispatched quantities per product ──────────────────
            $rows = [];
            foreach ($trip->dispatches as $dispatch) {
                foreach ($dispatch->items as $item) {
                    $pid = $item->product_id;
                    if (!isset($rows[$pid])) {
                        $rows[$pid] = ['dispatched' => 0.0, 'sold' => 0.0, 'returned' => 0.0];
                    }
                    $rows[$pid]['dispatched'] += (float) $item->quantity;
                }
            }

            // ── 2. Subtract sold (non-cancelled orders) ───────────────────
            foreach ($trip->saleOrders as $order) {
                if ($order->status === 'cancelled') continue;
                foreach ($order->items as $item) {
                    $pid = $item->product_id;
                    if (isset($rows[$pid])) {
                        $rows[$pid]['sold'] += (float) $item->quantity;
                    }
                }
            }

            // ── 3. Add sale returns ───────────────────────────────────────
            foreach ($trip->saleReturns as $return) {
                if ($return->status === 'cancelled') continue;
                foreach ($return->items as $item) {
                    $pid = $item->product_id;
                    if (isset($rows[$pid])) {
                        $rows[$pid]['returned'] += (float) $item->quantity;
                    }
                }
            }

            // ── 4. Build snapshot ─────────────────────────────────────────
            // For old trips we estimate actual_received = dispatched - sold + returned
            // (this is what should have been returned to branch)
            $snapshot = [];
            foreach ($rows as $pid => $row) {
                $expectedRemaining = max(0, $row['dispatched'] - $row['sold'] + $row['returned']);
                $snapshot[] = [
                    'product_id'      => $pid,
                    'actual_received' => $expectedRemaining,
                    'branch_added'    => $expectedRemaining,
                    'backfilled'      => true, // mark so we know it's estimated
                ];
            }

            $trip->update(['settlement_items' => $snapshot]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done. settlement_items backfilled successfully.');
        $this->warn('Note: backfilled values are estimated (dispatched - sold + returned). If actual received differed from expected, the reversal will use estimated values.');

        return self::SUCCESS;
    }
}

