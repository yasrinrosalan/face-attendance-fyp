<?php
// path: laravel_backend/database/migrations/2024_01_01_000003_create_attendance_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('session_title');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            // --- ADDED LINE ---
            // This will store the unique code like 'CS101-A'
            $table->string('referral_code')->unique()->nullable();
            // --- END ADDED LINE ---

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
