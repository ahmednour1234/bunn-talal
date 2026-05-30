<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('treasury_transaction_id')
                ->nullable()
                ->after('treasury_id')
                ->constrained('treasury_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['treasury_transaction_id']);
            $table->dropColumn('treasury_transaction_id');
        });
    }
};
