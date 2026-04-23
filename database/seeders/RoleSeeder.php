<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            [
                'display_name' => 'المدير العام',
                'description' => 'صلاحيات كاملة على النظام',
            ]
        );

        // Assign all permissions to super-admin
        $superAdmin->permissions()->sync(Permission::pluck('id'));
    }
}
