<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1);

         if ($user) {
            $role3 = Role::where('name', 'Administrador')->first();

            if ($role3) {
                $user->assignRole($role3);
            } else {
                echo "No se encontró el rol 'Reading'.";
            }
        } else {
            echo "No se encontró el usuario con ID 1.";
        }
    }
}
