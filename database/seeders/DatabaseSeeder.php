<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            UnitSeeder::class,
            AreaSeeder::class,
            AccountingSeeder::class,
            TaxSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
