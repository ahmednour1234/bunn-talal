<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\SaleOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCancelledOrdersCustomerBalance extends Command
{
    protected $signature = 'fix:cancelled-orders-balance {--dry-run : Show what will change without applying}';

    protected $description = 'Fix customer balances for already-cancelled sale orders (apply full total reversal instead of remaining-only)';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        // For each cancelled order that had payments (paid_amount > 0),
        // the old code only decremented (total - paid_amount).
        // The new code decrements the full total.
        // So the missing adjustment per order = paid_amount.
        $cancelledOrders = SaleOrder::where('status', 'cancelled')
            ->where('paid_amount', '>', 0)
            ->select('id', 'order_number', 'customer_id', 'total', 'paid_amount')
            ->get();

        if ($cancelledOrders->isEmpty()) {
            $this->info('No cancelled orders with paid amounts found. Nothing to fix.');
            return self::SUCCESS;
        }

        // Group by customer and sum the extra adjustment needed
        $adjustmentsByCustomer = $cancelledOrders
            ->groupBy('customer_id')
            ->map(fn($orders) => $orders->sum('paid_amount'));

        $this->table(
            ['Customer ID', 'Extra Decrement (paid_amount sum)', 'Affected Orders'],
            $adjustmentsByCustomer->map(fn($amount, $customerId) => [
                $customerId,
                number_format($amount, 2),
                $cancelledOrders->where('customer_id', $customerId)
                    ->pluck('order_number')
                    ->implode(', '),
            ])->values()->toArray()
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
