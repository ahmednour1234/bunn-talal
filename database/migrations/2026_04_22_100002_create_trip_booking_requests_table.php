<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'converted', 'cancelled'])->default('pending');
            $table->foreignId('converted_to_order_id')->nullable()->constrained('sale_orders')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trip_booking_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_request_id')->constrained('trip_booking_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_booking_request_items');
        Schema::dropIfExists('trip_booking_requests');
    }
};
