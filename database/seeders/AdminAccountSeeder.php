<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default superadmin if not exists
        if (! User::where('email', 'superadmin@gmail.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('superadmin123?'),
                'role' => 'superadmin',
            ]);
        }

        if (! User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin12345'),
                'role' => 'admin'
            ]);
        }

        if (! User::where('email', 'user@gmail.com')->exists()) {
            User::create([
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('user12345'),
                'role' => 'user'
            ]);
        }
    }
}
