<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            [
                'name'      => 'ضريبة القيمة المضافة',
                'rate'      => 15.00,
                'type'      => 'percentage',
                'is_active' => true,
            ],
            [
                'name'      => 'ضريبة مبيعات مخفضة',
                'rate'      => 5.00,
                'type'      => 'percentage',
                'is_active' => true,
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(['name' => $tax['name']], $tax);
        }
    }
}
