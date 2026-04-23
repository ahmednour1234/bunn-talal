<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->unsignedBigInteger('delegate_id')->nullable()->change();
        });

        Schema::table('collection_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_order_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->unsignedBigInteger('delegate_id')->nullable(false)->change();
        });
        Schema::table('collection_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_order_id')->nullable(false)->change();
        });
    }
};
