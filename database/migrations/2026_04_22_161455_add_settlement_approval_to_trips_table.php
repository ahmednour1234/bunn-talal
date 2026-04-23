<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->enum('settlement_status', ['pending', 'approved', 'rejected'])
                  ->nullable()->after('settled_at');
            $table->foreignId('settlement_approved_by')->nullable()->constrained('admins')->nullOnDelete()->after('settlement_status');
            $table->timestamp('settlement_approved_at')->nullable()->after('settlement_approved_by');
            $table->text('settlement_rejection_reason')->nullable()->after('settlement_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['settlement_approved_by']);
            $table->dropColumn(['settlement_status', 'settlement_approved_by', 'settlement_approved_at', 'settlement_rejection_reason']);
        });
    }
};
