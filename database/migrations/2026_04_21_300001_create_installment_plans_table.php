<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            $table->enum('party_type', ['customer', 'supplier']);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            // optional link to source document
            $table->enum('reference_type', ['sale_order', 'purchase_invoice', 'manual'])->default('manual');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('treasury_id')->nullable(); // default payment treasury
            $table->date('start_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2);      // total_amount - down_payment
            $table->unsignedInteger('installments_count');   // number of installments
            $table->decimal('installment_amount', 12, 2);    // per installment
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly', 'custom'])->default('monthly');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
            $table->foreign('treasury_id')->references('id')->on('treasuries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
