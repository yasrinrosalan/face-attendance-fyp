<?php
// path: laravel_backend/app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'face_template_path',
        'requesting_face_change', // <-- ADD THIS LINE
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- Relationships ---

    /**
     * Get the courses this lecturer teaches.
     */
    public function courses_lecturer_teaches()
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }

    /**
     * Get the attendance records for this student.
     */
    public function attendance_records()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    // --- Helper Functions ---
    public function isLecturer()
    {
        return $this->role === 'lecturer';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
