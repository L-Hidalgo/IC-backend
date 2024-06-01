<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create(['name' => 'Admin']);
        $role2 = Role::create(['name' => 'Moderators']);
        $role3 = Role::create(['name' => 'Reading']);

        $permission = Permission::create(['name' => 'dashboard'])->syncRoles([$role1, $role2, $role3]);

        $permission = Permission::create(['name' => 'admin.index'])->syncRoles([$role1]);
        $permission = Permission::create(['name' => 'admin.create'])->syncRoles([$role1]);
        $permission = Permission::create(['name' => 'admin.edit'])->syncRoles([$role1]);
        $permission = Permission::create(['name' => 'admin.destroy'])->syncRoles([$role1]);

        $permission = Permission::create(['name' => 'incorporaciones.index'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'incorporaciones.create'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'incorporaciones.edit'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'incorporaciones.destroy'])->syncRoles([$role1, $role2]);

        
    }
}
