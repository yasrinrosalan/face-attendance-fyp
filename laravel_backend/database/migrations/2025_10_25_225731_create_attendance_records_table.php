<?php
// path: laravel_backend/database/migrations/2024_01_01_000004_create_attendance_records_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            // The session the student attended
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->onDelete('cascade');
            // The student who attended
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            // The exact time they were marked present
            $table->timestamp('attended_at');
            $table->timestamps();

            // A student can only attend a session once
            $table->unique(['attendance_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};