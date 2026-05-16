<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\SaleOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCancelledOrdersCustomerBalance extends Command
{
    protected $signature = 'fix:cancelled-orders-balance {--dry-run : Show what will change without applying}';

    protected $description = 'Fix customer balances for already-cancelled sale orders (apply full total reversal)';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        // All cancelled orders affected customer balance on creation (+total).
        // New cancel logic decrements the full total (not just remaining).
        // Old logic decremented only (total - paid_amount).
        // Missing adjustment per order = paid_amount.
        //
        // For credit orders (paid_amount = 0): old and new logic are identical → no extra needed.
        // For partial orders (paid_amount > 0): need to additionally decrement by paid_amount.
        $cancelledOrders = SaleOrder::where('status', 'cancelled')
            ->select('id', 'order_number', 'customer_id', 'total', 'paid_amount')
            ->get();

        if ($cancelledOrders->isEmpty()) {
            $this->info('No cancelled orders found. Nothing to fix.');
            return self::SUCCESS;
        }

        $this->info("Found {$cancelledOrders->count()} cancelled order(s).");

        // Group by customer; adjustment = paid_amount (the extra bit not covered by old logic)
        $adjustmentsByCustomer = $cancelledOrders
            ->groupBy('customer_id')
            ->map(fn($orders) => $orders->sum('paid_amount'))
            ->filter(fn($amount) => $amount > 0); // credit-only customers need no change

        if ($adjustmentsByCustomer->isEmpty()) {
            $this->info('All cancelled orders are credit (paid_amount = 0). No balance corrections needed.');
            return self::SUCCESS;
        }

        $rows = $adjustmentsByCustomer->map(function ($amount, $customerId) use ($cancelledOrders) {
            $customer   = Customer::find($customerId);
            $orderNums  = $cancelledOrders
                ->where('customer_id', $customerId)
                ->where('paid_amount', '>', 0)
                ->pluck('order_number')
                ->implode(', ');

            return [
                $customerId,
                $customer?->name ?? '—',
                number_format($amount, 2),
                $orderNums,
            ];
        })->values()->toArray();

        $this->table(
            ['Customer ID', 'Name', 'Extra Balance Decrement', 'Affected Orders'],
            $rows
        );

        if ($isDryRun) {
            $this->warn('Dry run — no changes applied.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Apply these balance corrections to the database?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($adjustmentsByCustomer) {
            foreach ($adjustmentsByCustomer as $customerId => $extraDecrement) {
                Customer::where('id', $customerId)->decrement('balance', (float) $extraDecrement);
                $this->line("Customer #{$customerId} → balance decremented by {$extraDecrement}");
            }
        });

        $this->info('Done. Customer balances updated successfully.');

        return self::SUCCESS;
    }
}
