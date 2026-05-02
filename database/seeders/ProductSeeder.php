<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get units by name for reference
        $kg   = Unit::where('name', 'كيلوجرام')->first();
        $gram = Unit::where('name', 'جرام')->first();
        $pcs  = Unit::first(); // fallback to first unit if custom units not found

        $defaultUnit = $kg ?? $gram ?? $pcs;
        $unitId = $defaultUnit?->id ?? 1;

        // Get categories by name
        $yemeni    = Category::where('name', 'قهوة يمنية')->first();
        $arabic    = Category::where('name', 'قهوة عربية')->first();
        $specialty = Category::where('name', 'قهوة مختصة')->first();
        $spices    = Category::where('name', 'بهارات وتوابل')->first();
        $tools     = Category::where('name', 'مستلزمات القهوة')->first();
        $gifts     = Category::where('name', 'هدايا وعروض')->first();

        $products = [
            // قهوة يمنية
            [
                'name'          => 'بن حرازي أخضر - 1 كيلو',
                'category_id'   => $yemeni?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 25000,
                'selling_price' => 35000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            [
                'name'          => 'بن صنعاني محمص - 500 جرام',
                'category_id'   => $yemeni?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 12000,
                'selling_price' => 18000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            [
                'name'          => 'بن ريمي طبيعي - 1 كيلو',
                'category_id'   => $yemeni?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 22000,
                'selling_price' => 32000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            // قهوة عربية
            [
                'name'          => 'قهوة عربية مطحونة - 250 جرام',
                'category_id'   => $arabic?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 4000,
                'selling_price' => 6500,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            [
                'name'          => 'قهوة سعودية مع الهيل - 500 جرام',
                'category_id'   => $arabic?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 7000,
                'selling_price' => 11000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            // قهوة مختصة
            [
                'name'          => 'بن سبيشالتي يمني - 250 جرام',
                'category_id'   => $specialty?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 15000,
                'selling_price' => 22000,
                'discount'      => 5,
                'discount_type' => 'percentage',
                'is_active'     => true,
            ],
            // بهارات وتوابل
            [
                'name'          => 'هيل يمني أصلي - 250 جرام',
                'category_id'   => $spices?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 5000,
                'selling_price' => 8000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            [
                'name'          => 'زنجبيل مجفف - 250 جرام',
                'category_id'   => $spices?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 3000,
                'selling_price' => 5000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            // مستلزمات القهوة
            [
                'name'          => 'دلة قهوة عربية نحاسية',
                'category_id'   => $tools?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 8000,
                'selling_price' => 14000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            [
                'name'          => 'فنجان قهوة عربية - طقم 6 قطع',
                'category_id'   => $tools?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 6000,
                'selling_price' => 10000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'is_active'     => true,
            ],
            // هدايا وعروض
            [
                'name'          => 'طقم هدية قهوة يمنية فاخرة',
                'category_id'   => $gifts?->id ?? 1,
                'unit_id'       => $unitId,
                'cost_price'    => 18000,
                'selling_price' => 28000,
                'discount'      => 10,
                'discount_type' => 'percentage',
                'is_active'     => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
