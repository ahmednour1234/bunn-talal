<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegate_product', function (Blueprint $table) {
            $table->dropForeign(['delegate_id']);
            $table->foreign('delegate_id')->references('id')->on('delegates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delegate_product', function (Blueprint $table) {
            $table->dropForeign(['delegate_id']);
            $table->foreign('delegate_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }
};
