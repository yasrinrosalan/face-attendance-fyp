<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'late_tolerance' to sessions if missing
        if (!Schema::hasColumn('attendance_sessions', 'late_tolerance')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->integer('late_tolerance')->default(15)->after('ends_at');
            });
        }

        // Add 'status' to records if missing
        if (!Schema::hasColumn('attendance_records', 'status')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->enum('status', ['present', 'late', 'absent'])->default('present')->after('attended_at');
            });
        }
    }

    public function down(): void
    {
        // Optional: drop columns if needed
    }
};
