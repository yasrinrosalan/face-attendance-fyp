<?php
// path: laravel_backend/database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- ADDED ADMIN ---
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'), // password is 'password'
            'role' => 'admin',
        ]);
        // --- END ADDED ---

        // --- Create a dummy Lecturer ---
        User::create([
            'name' => 'Dr. Alan Smith',
            'email' => 'lecturer@demo.com',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
        ]);

        // --- Create a dummy Student ---
        User::create([
            'name' => 'John Doe',
            'email' => 'student@demo.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // --- Create another dummy Student ---
        User::create([
            'name' => 'Jane Roe',
            'email' => 'student2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }
}