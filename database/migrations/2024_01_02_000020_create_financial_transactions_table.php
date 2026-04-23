<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // expense, revenue
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('treasury_id')->nullable()->constrained('treasuries')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
