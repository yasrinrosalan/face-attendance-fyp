<?php
// path: laravel_backend/app/Http/Controllers/AnalyticsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\User;
use App\Models\AttendanceSession;

class AnalyticsController extends Controller
{
    /**
     * Show the analytics dashboard.
     */
    public function show()
    {
        $user = Auth::user();

        // --- 1. Data for "Attendance Over Time" (Line Chart) ---

        // Get all sessions, with their attendance count
        $sessionsQuery = AttendanceSession::query()
            ->withCount('attendance_records')
            ->orderBy('starts_at', 'asc');

        // If the user is a lecturer, only show their sessions
        if ($user->isLecturer()) {
            $sessionsQuery->whereHas('course', function ($query) use ($user) {
                $query->where('lecturer_id', $user->id);
            });
        }

        $sessions = $sessionsQuery->get();

        // Format data for Chart.js
        $attendanceOverTime = [
            'labels' => $sessions->map(function ($session) {
                // Format the date (e.g., "Nov 02") and add the session title
                return $session->starts_at->format('M d') . ' - ' . $session->session_title;
            }),
            'data' => $sessions->pluck('attendance_records_count'),
        ];


        // --- 2. Data for "Attendance by Course" (Doughnut Chart) ---

        // Get all courses
        $coursesQuery = Course::query();

        if ($user->isLecturer()) {
            $coursesQuery->where('lecturer_id', $user->id);
        }

        // Eager load the relationships we need
        $courses = $coursesQuery->with('attendance_sessions.attendance_records')->get();

        $courseLabels = [];
        $courseData = [];

        foreach ($courses as $course) {
            // Add the course code to labels
            $courseLabels[] = $course->course_code;
            // Sum up all records from all sessions for this course
            $courseData[] = $course->attendance_sessions->sum(function ($session) {
                return $session->attendance_records->count();
            });
        }

        $attendanceByCourse = [
            'labels' => $courseLabels,
            'data' => $courseData,
        ];


        // --- 3. Data for "At-Risk" (Least Active) Students (MODIFIED) ---

        if ($user->isLecturer()) {
            // LECTURER VIEW: Get students relevant to THIS lecturer's courses

            // 1. Get IDs of all sessions created by this lecturer
            $mySessionIds = AttendanceSession::whereHas('course', function($q) use ($user) {
                $q->where('lecturer_id', $user->id);
            })->pluck('id');

            // 2. Find students who have at least one record in these sessions
            // AND count their attendance ONLY for these sessions
            $leastActiveStudents = User::whereHas('attendance_records', function($q) use ($mySessionIds) {
                $q->whereIn('attendance_session_id', $mySessionIds);
            })
            ->withCount(['attendance_records' => function($q) use ($mySessionIds) {
                // Only count attendance for MY sessions
                $q->whereIn('attendance_session_id', $mySessionIds);
            }])
            ->orderBy('attendance_records_count', 'asc') // Lowest first
            ->limit(5)
            ->get();

        } else {
            // ADMIN VIEW: Global lowest attendance
            $leastActiveStudents = User::where('role', 'student')
                ->withCount('attendance_records')
                ->orderBy('attendance_records_count', 'asc')
                ->limit(5)
                ->get();
        }


        // Pass all data to the new view
        return view('analytics.dashboard', [
            'attendanceOverTime' => $attendanceOverTime,
            'attendanceByCourse' => $attendanceByCourse,
            'leastActiveStudents' => $leastActiveStudents,
        ]);
    }
}