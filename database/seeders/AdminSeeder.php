<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@bintalal.com'],
            [
                'name' => 'مدير النظام',
                'password' => 'password',
            ]
        );

        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $admin->syncRoles([$superAdminRole->id]);
        }
    }
}
