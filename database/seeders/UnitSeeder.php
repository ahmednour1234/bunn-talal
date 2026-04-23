<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // === الوزن (Weight) ===
        $gram = Unit::create([
            'name' => 'جرام',
            'symbol' => 'g',
            'type' => 'weight',
            'base_unit_id' => null,
            'conversion_factor' => 1,
            'is_active' => true,
        ]);

        Unit::create([
            'name' => 'كيلوجرام',
            'symbol' => 'kg',
            'type' => 'weight',
            'base_unit_id' => $gram->id,
            'conversion_factor' => 1000,
            'is_active' => true,
        ]);

        Unit::create([
            'name' => 'طن',
            'symbol' => 'ton',
            'type' => 'weight',
            'base_unit_id' => $gram->id,
            'conversion_factor' => 1000000,
            'is_active' => true,
        ]);

        // === الحجم (Volume) ===
        $ml = Unit::create([
            'name' => 'مليلتر',
            'symbol' => 'ml',
            'type' => 'volume',
            'base_unit_id' => null,
            'conversion_factor' => 1,
            'is_active' => true,
        ]);

        Unit::create([
            'name' => 'لتر',
            'symbol' => 'L',
            'type' => 'volume',
            'base_unit_id' => $ml->id,
            'conversion_factor' => 1000,
            'is_active' => true,
        ]);

        // === الكمية (Quantity) ===
        $piece = Unit::create([
            'name' => 'قطعة',
            'symbol' => 'pcs',
            'type' => 'quantity',
            'base_unit_id' => null,
            'conversion_factor' => 1,
            'is_active' => true,
        ]);

        Unit::create([
            'name' => 'دستة',
            'symbol' => 'dozen',
            'type' => 'quantity',
            'base_unit_id' => $piece->id,
            'conversion_factor' => 12,
            'is_active' => true,
        ]);

        Unit::create([
            'name' => 'كرتونة',
            'symbol' => 'ctn',
            'type' => 'quantity',
            'base_unit_id' => $piece->id,
            'conversion_factor' => 24,
            'is_active' => true,
        ]);

        // === الطول (Length) ===
        $cm = Unit::create([
            'name' => 'سنتيمتر',
            'symbol' => 'cm',
            'type' => 'length',
            'base_unit_id' => null,
            'conversion_factor' => 1,
            'is_active' => true,
        ]);

        Unit::create([
            'name' => 'متر',
            'symbol' => 'm',
            'type' => 'length',
            'base_unit_id' => $cm->id,
            'conversion_factor' => 100,
            'is_active' => true,
        ]);
    }
}
