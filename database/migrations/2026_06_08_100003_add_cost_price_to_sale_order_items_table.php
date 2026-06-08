<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_order_items', function (Blueprint $table) {
            // Snapshot of the branch cost price at the moment of sale, for accurate profit reporting.
            $table->decimal('cost_price', 12, 4)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('sale_order_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
