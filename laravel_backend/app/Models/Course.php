<?php
// path: laravel_backend/app/Models/Course.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [ 'course_code', 'course_name', 'lecturer_id', 'academic_year', 'semester' ];

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    // --- REMOVED 'students' relationship ---

    public function attendance_sessions()
    {
        return $this->hasMany(AttendanceSession::class, 'course_id');
    }

    public function students()
    {
        // This tells Laravel to use the 'course_student' pivot table
        // to find Users (students) attached to this Course.
        return $this->belongsToMany(User::class, 'course_student', 'course_id', 'student_id');
    }
}