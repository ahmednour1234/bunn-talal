<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Stores per-product snapshot at settlement time:
            // [{product_id, actual_received, branch_added}]
            $table->json('settlement_items')->nullable()->after('settlement_notes');
            // Stores the treasury transaction ID created at settlement for reversal
            $table->unsignedBigInteger('settlement_treasury_transaction_id')->nullable()->after('settlement_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['settlement_items', 'settlement_treasury_transaction_id']);
        });
    }
};
