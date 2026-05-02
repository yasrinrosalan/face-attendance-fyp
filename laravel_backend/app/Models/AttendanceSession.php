<?php
// path: laravel_backend/app/Models/AttendanceSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'session_title',
        'mode', // <--- ADD THIS
        'referral_code',
        'starts_at',
        'ends_at',
        'late_tolerance',
        'week_number',
        'mode',
        'latitude',     // Add this
        'longitude',   // Add this
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function attendance_records()
    {
        return $this->hasMany(AttendanceRecord::class, 'attendance_session_id');
    }

    public function isActive()
    {
        $now = now();
        return $now->gte($this->starts_at) && $now->lte($this->ends_at);
    }
}