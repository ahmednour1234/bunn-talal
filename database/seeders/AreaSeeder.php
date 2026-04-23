<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'القاهرة',
            'الجيزة',
            'الإسكندرية',
            'الدقهلية',
            'الشرقية',
            'القليوبية',
            'الغربية',
            'المنوفية',
            'البحيرة',
            'كفر الشيخ',
            'دمياط',
            'بورسعيد',
            'الإسماعيلية',
            'السويس',
            'الفيوم',
            'بني سويف',
            'المنيا',
            'أسيوط',
            'سوهاج',
            'قنا',
            'الأقصر',
            'أسوان',
            'البحر الأحمر',
            'الوادي الجديد',
            'مطروح',
            'شمال سيناء',
            'جنوب سيناء',
        ];

        foreach ($areas as $area) {
            Area::create([
                'name' => $area,
                'is_active' => true,
            ]);
        }
    }
}
