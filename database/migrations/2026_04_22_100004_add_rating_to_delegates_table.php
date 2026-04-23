<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->decimal('rating', 5, 2)->default(100)->after('is_active');
            $table->decimal('total_cash_deficit', 18, 2)->default(0)->after('rating');
            $table->decimal('total_product_deficit', 18, 2)->default(0)->after('total_cash_deficit');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropColumn(['rating', 'total_cash_deficit', 'total_product_deficit']);
        });
    }
};
