<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->decimal('max_discount_percentage', 5, 2)->default(0)->after('sales_commission_rate')
                ->comment('الحد الأقصى لنسبة الخصم المسموح بها للمندوب (0 = بدون حد)');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropColumn('max_discount_percentage');
        });
    }
};
