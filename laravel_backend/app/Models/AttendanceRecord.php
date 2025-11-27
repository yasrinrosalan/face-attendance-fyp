<?php
// path: laravel_backend/app/Models/AttendanceRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'attended_at',
        'status',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    /**
     * Get the session this record belongs to.
     */
    public function attendance_session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    /**
     * Get the student for this record.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
