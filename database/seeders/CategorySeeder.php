<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'قهوة يمنية',         'is_active' => true],
            ['name' => 'قهوة عربية',          'is_active' => true],
            ['name' => 'قهوة مختصة',         'is_active' => true],
            ['name' => 'قهوة سبيشالتي',      'is_active' => true],
            ['name' => 'شاي وأعشاب',         'is_active' => true],
            ['name' => 'بهارات وتوابل',      'is_active' => true],
            ['name' => 'مستلزمات القهوة',    'is_active' => true],
            ['name' => 'هدايا وعروض',        'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
