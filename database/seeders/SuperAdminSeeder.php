<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'super admin', 'guard_name' => 'web']
        );

        $user = User::firstOrCreate(
            ['email' => 'superadmin@reca.play.com'],
            [
                'role_id' => $role->id,
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $user->assignRole($role->name);
    }

    public function rollback(): void
    {
        $user = User::where('email', 'superadmin@reca.play.com')->first();
        if ($user) {
            $user->removeRole('super admin');
            $user->delete();
        }
    }
}
