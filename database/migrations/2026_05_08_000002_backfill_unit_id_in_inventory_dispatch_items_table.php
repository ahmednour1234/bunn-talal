<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fill unit_id from the product's own unit for all existing items that have no unit_id
        DB::statement('
            UPDATE inventory_dispatch_items idi
            JOIN products p ON p.id = idi.product_id
            SET idi.unit_id = p.unit_id
            WHERE idi.unit_id IS NULL
        ');
    }

    public function down(): void
    {
        // Not reversible — setting back to NULL would lose data
    }
};
