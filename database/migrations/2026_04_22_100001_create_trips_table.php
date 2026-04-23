<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number')->unique();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->enum('status', [
                'draft',
                'active',
                'in_transit',
                'returning',
                'settled',
                'cancelled',
            ])->default('draft');

            $table->date('start_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();

            $table->decimal('total_dispatched_value', 18, 2)->default(0);
            $table->decimal('total_invoiced', 18, 2)->default(0);
            $table->decimal('total_collected', 18, 2)->default(0);
            $table->decimal('total_returned_value', 18, 2)->default(0);

            // Settlement fields
            $table->decimal('settlement_cash_expected', 18, 2)->default(0);
            $table->decimal('settlement_cash_actual', 18, 2)->nullable();
            $table->decimal('settlement_cash_deficit', 18, 2)->default(0);
            $table->decimal('settlement_product_deficit', 18, 2)->default(0);
            $table->text('settlement_notes')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('settled_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
