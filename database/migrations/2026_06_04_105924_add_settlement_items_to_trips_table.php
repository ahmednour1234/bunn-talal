<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: settlement_items column was considered but not needed —
     * reversal is computed live from trip relationships (dispatches/saleOrders/saleReturns).
     */
    public function up(): void
    {
        // No columns needed — reversal uses live trip data
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to drop
    }
};
