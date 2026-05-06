<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegate_loans', function (Blueprint $table) {
            $table->foreignId('sale_order_id')->nullable()->after('admin_id')->constrained('sale_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delegate_loans', function (Blueprint $table) {
            $table->dropForeign(['sale_order_id']);
            $table->dropColumn('sale_order_id');
        });
    }
};
