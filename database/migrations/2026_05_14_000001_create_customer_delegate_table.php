<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Many-to-many: customers <-> delegates
        Schema::create('customer_delegate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->unique(['customer_id', 'delegate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_delegate');
    }
};
