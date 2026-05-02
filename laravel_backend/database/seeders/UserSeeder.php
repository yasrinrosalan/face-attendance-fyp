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
            'name' => 'Dr. Nabil',
            'email' => 'lecturer@demo.com',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
        ]);

        // --- Create a dummy Student ---
        User::create([
            'name' => 'YASRIN',
            'student_id' => 'CB23102',
            'email' => 'student@demo.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // --- Create another dummy Student ---
        User::create([
            'name' => 'AMIR',
            'student_id' => 'CB23103',
            'email' => 'student2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }
}
