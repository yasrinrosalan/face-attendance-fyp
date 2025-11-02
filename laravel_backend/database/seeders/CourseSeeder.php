<?php
// path: laravel_backend/database/seeders/CourseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Find the lecturer we created
        $lecturer = User::where('email', 'lecturer@demo.com')->first();

        if ($lecturer) {
            // Create a course for this lecturer
            Course::create([
                'course_code' => 'CS101',
                'course_name' => 'Introduction to Computer Science',
                'lecturer_id' => $lecturer->id,
            ]);

            // Create another course
            Course::create([
                'course_code' => 'MATH202',
                'course_name' => 'Discrete Mathematics',
                'lecturer_id' => $lecturer->id,
            ]);
        }
    }
}
