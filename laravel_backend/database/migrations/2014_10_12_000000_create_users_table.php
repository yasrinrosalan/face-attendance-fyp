<?php
// path: laravel_backend/database/migrations/2014_10_12_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- MODIFIED LINE ---
            // Add 'admin' as a role option
            $table->enum('role', ['student', 'lecturer', 'admin'])->default('student');
            // --- END MODIFIED LINE ---

            $table->boolean('requesting_face_change')->default(false);

            $table->string('face_template_path')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};