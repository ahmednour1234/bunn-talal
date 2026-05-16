<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('settlement_treasury_id')
                  ->nullable()
                  ->after('cash_custody_treasury_id')
                  ->constrained('treasuries')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['settlement_treasury_id']);
            $table->dropColumn('settlement_treasury_id');
        });
    }
};
