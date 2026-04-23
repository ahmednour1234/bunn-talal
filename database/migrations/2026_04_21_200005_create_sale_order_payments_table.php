<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treasury_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_order_payments');
    }
};
