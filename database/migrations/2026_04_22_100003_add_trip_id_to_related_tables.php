<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add trip_id to inventory_dispatches
        Schema::table('inventory_dispatches', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('admin_id')->constrained('trips')->nullOnDelete();
        });

        // Add trip_id to collections
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('admin_id')->constrained('trips')->nullOnDelete();
        });

        // Add trip_id to sale_orders
        Schema::table('sale_orders', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('delegate_id')->constrained('trips')->nullOnDelete();
        });

        // Add trip_id to sale_returns
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('admin_id')->constrained('trips')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
        });
        Schema::table('sale_orders', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
        });
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
        });
        Schema::table('inventory_dispatches', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
        });
    }
};
