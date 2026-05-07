<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_dispatch_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_dispatch_items', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Unit::class);
            $table->dropColumn('unit_id');
        });
    }
};
