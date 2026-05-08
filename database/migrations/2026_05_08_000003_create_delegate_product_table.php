<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegate_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['delegate_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegate_product');
    }
};
