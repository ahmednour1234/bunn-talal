<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_dispatches', function (Blueprint $table) {
            $table->foreignId('treasury_transaction_id')
                ->nullable()
                ->after('actual_sales')
                ->constrained('treasury_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_dispatches', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\TreasuryTransaction::class, 'treasury_transaction_id');
            $table->dropColumn('treasury_transaction_id');
        });
    }
};
