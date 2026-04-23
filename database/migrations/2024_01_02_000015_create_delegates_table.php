<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('national_id', 30)->nullable();
            $table->string('national_id_image')->nullable();
            $table->string('password');
            $table->decimal('credit_sales_limit', 12, 2)->default(0);
            $table->decimal('cash_custody', 12, 2)->default(0);
            $table->decimal('total_collected', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('sales_commission_rate', 5, 2)->default(0);
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('location_updated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Many-to-many: delegates <-> branches
        Schema::create('delegate_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unique(['delegate_id', 'branch_id']);
        });

        // Many-to-many: delegates <-> areas
        Schema::create('delegate_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->unique(['delegate_id', 'area_id']);
        });

        // Many-to-many: delegates <-> categories
        Schema::create('delegate_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unique(['delegate_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegate_category');
        Schema::dropIfExists('delegate_area');
        Schema::dropIfExists('delegate_branch');
        Schema::dropIfExists('delegates');
    }
};
