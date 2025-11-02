<?php
// path: laravel_backend/database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call the seeders we created
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
        ]);
    }
}
