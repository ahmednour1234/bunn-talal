<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed')->after('discount');
            $table->foreignId('tax_id')->nullable()->after('discount_type')->constrained('taxes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn(['discount_type', 'tax_id']);
        });
    }
};
