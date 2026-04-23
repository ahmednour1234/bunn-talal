<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
            $table->softDeletes();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('longitude');
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('description');
            $table->softDeletes();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
