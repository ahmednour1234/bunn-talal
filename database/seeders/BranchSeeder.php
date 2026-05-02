<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name'      => 'الفرع الرئيسي - صنعاء',
                'phone'     => '+967711000001',
                'email'     => 'main@bun.ye',
                'latitude'  => 15.3694,
                'longitude' => 44.1910,
                'is_active' => true,
            ],
            [
                'name'      => 'فرع عدن',
                'phone'     => '+967711000002',
                'email'     => 'aden@bun.ye',
                'latitude'  => 12.7794,
                'longitude' => 45.0367,
                'is_active' => true,
            ],
            [
                'name'      => 'فرع تعز',
                'phone'     => '+967711000003',
                'email'     => 'taiz@bun.ye',
                'latitude'  => 13.5790,
                'longitude' => 44.0209,
                'is_active' => true,
            ],
            [
                'name'      => 'فرع الحديدة',
                'phone'     => '+967711000004',
                'email'     => 'hodeidah@bun.ye',
                'latitude'  => 14.7978,
                'longitude' => 42.9519,
                'is_active' => true,
            ],
            [
                'name'      => 'فرع إب',
                'phone'     => '+967711000005',
                'email'     => 'ibb@bun.ye',
                'latitude'  => 13.9780,
                'longitude' => 44.1836,
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(
                ['name' => $branch['name']],
                $branch
            );
        }
    }
}
