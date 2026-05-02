<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchProductSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('name');
        $products = Product::with('unit')->get()->keyBy('name');

        if ($branches->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No branches or products found. Run BranchSeeder and ProductSeeder first.');
            return;
        }

        // [branch_name => [product_name => quantity_in_product_unit]]
        $stock = [
            'الفرع الرئيسي - صنعاء' => [
                'بن حرازي أخضر'       => 200,
                'بن صنعاني محمص'      => 150,
                'بن ريمي طبيعي'       => 120,
                'قهوة عربية مطحونة'   => 100,
                'قهوة إثيوبية'        => 80,
                'قهوة كولومبية'       => 80,
                'بهارات القهوة اليمنية' => 60,
                'هيل مطحون'           => 50,
                'ماكينة قهوة يدوية'   => 15,
                'مجموعة أكواب قهوة عربية' => 20,
                'صندوق هدايا القهوة اليمنية' => 10,
            ],
            'فرع عدن' => [
                'بن حرازي أخضر'       => 120,
                'بن صنعاني محمص'      => 100,
                'بن ريمي طبيعي'       => 80,
                'قهوة عربية مطحونة'   => 70,
                'قهوة إثيوبية'        => 50,
                'بهارات القهوة اليمنية' => 40,
                'هيل مطحون'           => 30,
                'ماكينة قهوة يدوية'   => 8,
                'صندوق هدايا القهوة اليمنية' => 5,
            ],
            'فرع تعز' => [
                'بن حرازي أخضر'       => 100,
                'بن صنعاني محمص'      => 90,
                'بن ريمي طبيعي'       => 70,
                'قهوة عربية مطحونة'   => 60,
                'قهوة كولومبية'       => 40,
                'بهارات القهوة اليمنية' => 35,
                'هيل مطحون'           => 25,
                'ماكينة قهوة يدوية'   => 6,
                'مجموعة أكواب قهوة عربية' => 10,
            ],
            'فرع الحديدة' => [
                'بن حرازي أخضر'       => 80,
                'بن صنعاني محمص'      => 70,
                'قهوة عربية مطحونة'   => 50,
                'قهوة إثيوبية'        => 40,
                'بهارات القهوة اليمنية' => 30,
                'هيل مطحون'           => 20,
                'صندوق هدايا القهوة اليمنية' => 4,
            ],
            'فرع إب' => [
                'بن حرازي أخضر'       => 70,
                'بن صنعاني محمص'      => 60,
                'بن ريمي طبيعي'       => 50,
                'قهوة عربية مطحونة'   => 40,
                'بهارات القهوة اليمنية' => 25,
                'هيل مطحون'           => 20,
                'ماكينة قهوة يدوية'   => 4,
            ],
        ];

        foreach ($stock as $branchName => $productStock) {
            $branch = $branches->get($branchName);
            if (!$branch) {
                continue;
            }

            foreach ($productStock as $productName => $quantity) {
                $product = $products->get($productName);
                if (!$product) {
                    continue;
                }

                DB::table('branch_product')->upsert(
                    [
                        'branch_id'  => $branch->id,
                        'product_id' => $product->id,
                        'unit_id'    => $product->unit_id,
                        'quantity'   => $quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    ['branch_id', 'product_id'],
                    ['quantity', 'unit_id', 'updated_at']
                );
            }
        }

        $this->command->info('Branch product stock seeded successfully.');
    }
}
