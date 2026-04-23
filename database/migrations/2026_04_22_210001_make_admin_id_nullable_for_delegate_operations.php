<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow delegate-created records with no admin_id
        Schema::table('sale_orders', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
        });

        Schema::table('sale_order_payments', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_order_payments', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
        });

        Schema::table('sale_orders', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
        });
    }
};
