<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get units by name for reference
        $kg   = Unit::where('name', 'كيلوجرام')->first();
        $gram = Unit::where('name', 'جرام')->first();
        $pcs  = Unit::where('name', 'قطعة')->first();

        $kgId   = $kg?->id   ?? $gram?->id ?? 1;
        $gramId = $gram?->id ?? $kgId;
        $pcsId  = $pcs?->id  ?? $kgId;

        // Get taxes by name
        $vat      = Tax::where('name', 'ضريبة القيمة المضافة')->first();
        $reduced  = Tax::where('name', 'ضريبة مبيعات مخفضة')->first();

        // Get categories by name
        $yemeni    = Category::where('name', 'قهوة يمنية')->first();
        $arabic    = Category::where('name', 'قهوة عربية')->first();
        $specialty = Category::where('name', 'قهوة مختصة')->first();
        $spices    = Category::where('name', 'بهارات وتوابل')->first();
        $tools     = Category::where('name', 'مستلزمات القهوة')->first();
        $gifts     = Category::where('name', 'هدايا وعروض')->first();

        $products = [
            // قهوة يمنية (السعر بالكيلوجرام)
            [
                'name'          => 'بن حرازي أخضر',
                'category_id'   => $yemeni?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 25000,
                'selling_price' => 35000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $reduced?->id,
                'is_active'     => true,
            ],
            [
                'name'          => 'بن صنعاني محمص',
                'category_id'   => $yemeni?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 24000,
                'selling_price' => 36000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $reduced?->id,
                'is_active'     => true,
            ],
            [
                'name'          => 'بن ريمي طبيعي',
                'category_id'   => $yemeni?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 22000,
                'selling_price' => 32000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $reduced?->id,
                'is_active'     => true,
            ],
            // قهوة عربية
            [
                'name'          => 'قهوة عربية مطحونة',
                'category_id'   => $arabic?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 16000,
                'selling_price' => 26000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $reduced?->id,
                'is_active'     => true,
            ],
            [
                'name'          => 'قهوة سعودية بالهيل',
                'category_id'   => $arabic?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 14000,
                'selling_price' => 22000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $reduced?->id,
                'is_active'     => true,
            ],
            // قهوة مختصة
            [
                'name'          => 'بن سبيشالتي يمني',
                'category_id'   => $specialty?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 60000,
                'selling_price' => 88000,
                'discount'      => 5,
                'discount_type' => 'percentage',
                'tax_id'        => $vat?->id,
                'is_active'     => true,
            ],
            // بهارات وتوابل (السعر بالكيلوجرام)
            [
                'name'          => 'هيل يمني أصلي',
                'category_id'   => $spices?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 20000,
                'selling_price' => 32000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => null,
                'is_active'     => true,
            ],
            [
                'name'          => 'زنجبيل مجفف',
                'category_id'   => $spices?->id ?? 1,
                'unit_id'       => $kgId,
                'cost_price'    => 12000,
                'selling_price' => 20000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => null,
                'is_active'     => true,
            ],
            // مستلزمات القهوة (السعر بالقطعة)
            [
                'name'          => 'دلة قهوة عربية نحاسية',
                'category_id'   => $tools?->id ?? 1,
                'unit_id'       => $pcsId,
                'cost_price'    => 8000,
                'selling_price' => 14000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $vat?->id,
                'is_active'     => true,
            ],
            [
                'name'          => 'طقم فناجين قهوة عربية - 6 قطع',
                'category_id'   => $tools?->id ?? 1,
                'unit_id'       => $pcsId,
                'cost_price'    => 6000,
                'selling_price' => 10000,
                'discount'      => 0,
                'discount_type' => 'fixed',
                'tax_id'        => $vat?->id,
                'is_active'     => true,
            ],
            // هدايا وعروض (السعر بالقطعة)
            [
                'name'          => 'طقم هدية قهوة يمنية فاخرة',
                'category_id'   => $gifts?->id ?? 1,
                'unit_id'       => $pcsId,
                'cost_price'    => 18000,
                'selling_price' => 28000,
                'discount'      => 10,
                'discount_type' => 'percentage',
                'tax_id'        => $vat?->id,
                'is_active'     => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
