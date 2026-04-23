<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('cash_custody_amount', 15, 2)->default(0)->after('notes');
            $table->foreignId('cash_custody_treasury_id')
                  ->nullable()
                  ->constrained('treasuries')
                  ->nullOnDelete()
                  ->after('cash_custody_amount');
            $table->string('cash_custody_note')->nullable()->after('cash_custody_treasury_id');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_custody_treasury_id');
            $table->dropColumn(['cash_custody_amount', 'cash_custody_note']);
        });
    }
};
