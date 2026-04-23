<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delegate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'dispatched', 'partial_return', 'returned', 'settled'])->default('pending');
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('expected_sales', 12, 2)->default(0);
            $table->decimal('actual_sales', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_dispatches');
    }
};
