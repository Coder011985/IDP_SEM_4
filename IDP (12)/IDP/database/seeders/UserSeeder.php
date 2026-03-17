<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Main Admin',
            'email' => 'admin@idp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 1,
        ]);

        // Client
        User::create([
            'name' => 'Test Client',
            'email' => 'client@idp.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 1,
        ]);

        // Designer
        User::create([
            'name' => 'Test Designer',
            'email' => 'designer@idp.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'specialization' => 'residential',
            'status' => 1,
        ]);
    }
}
