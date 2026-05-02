<?php
// path: laravel_backend/database/migrations/2024_01_01_000001_create_courses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code');
            $table->string('course_name');

            $table->string('academic_year'); // e.g., "2025/2026"
            $table->integer('semester');     // e.g., 1 or 2

            // Link this course to the lecturer (a user)
            $table->foreignId('lecturer_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};