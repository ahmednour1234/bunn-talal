<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasury_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treasury_id')->constrained('treasuries')->cascadeOnDelete();
            $table->string('type', 20); // deposit, withdrawal
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->string('reference_number', 50)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_transactions');
    }
};
