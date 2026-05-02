<?php
// path: laravel_backend/app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'student_id',
        'email',
        'password',
        'role',
        'face_template_path',
        'requesting_face_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ... (Relationships and Helper functions remain unchanged) ...
    public function courses_lecturer_teaches() { return $this->hasMany(Course::class, 'lecturer_id'); }
    public function attendance_records() { return $this->hasMany(AttendanceRecord::class, 'student_id'); }
    public function isLecturer() { return $this->role === 'lecturer'; }
    public function isStudent() { return $this->role === 'student'; }
    public function isAdmin() { return $this->role === 'admin'; }

    // Add this inside your User model
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_student', 'student_id', 'course_id');
    }
}
