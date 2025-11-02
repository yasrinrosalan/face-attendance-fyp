<?php
// path: laravel_backend/database/migrations/2024_01_01_000002_create_course_student_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// This is a pivot table to link many students to many courses
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_student', function (Blueprint $table) {
            $table->id();
            // Link to the course
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            // Link to the student (a user)
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Ensure a student can only join a course once
            $table->unique(['course_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_student');
    }
};