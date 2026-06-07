<?php
// path: laravel_backend/app/Http/Controllers/AnalyticsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\User;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;

class AnalyticsController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();

        // Catch the course filter from the URL dropdown
        $selectedCourseId = $request->query('course_id');

        // --- 0. Get Courses for the Dropdown Filter ---
        $availableCourses = collect();
        if ($user->isLecturer()) {
            $availableCourses = Course::where('lecturer_id', $user->id)->get();
        }

        // --- 1. Data for "Attendance Over Time" (Line Chart) ---
        $sessionsQuery = AttendanceSession::query()
            ->withCount('attendance_records')
            ->orderBy('starts_at', 'asc');

        if ($user->isLecturer()) {
            if ($selectedCourseId) {
                // Filter by specific course
                $sessionsQuery->where('course_id', $selectedCourseId);
            } else {
                // Show all lecturer's courses
                $sessionsQuery->whereHas('course', function ($query) use ($user) {
                    $query->where('lecturer_id', $user->id);
                });
            }
        }

        $sessions = $sessionsQuery->get();

        $attendanceOverTime = [
            'labels' => $sessions->map(function ($session) {
                return $session->starts_at->format('M d') . ' - ' . $session->session_title;
            }),
            'data' => $sessions->pluck('attendance_records_count'),
        ];


        // --- 2. Data for Doughnut Chart (CONTEXT-AWARE FEATURE) ---
        $doughnutLabels = [];
        $doughnutData = [];
        $doughnutTitle = 'Course Distribution';

        if ($selectedCourseId) {
            // CONTEXT A: A specific course is selected -> Show Present vs Late vs Absent
            $doughnutTitle = 'Attendance Status';

            // Get all attendance records specifically for this course
            $records = AttendanceRecord::whereHas('attendance_session', function($q) use ($selectedCourseId) {
                $q->where('course_id', $selectedCourseId);
            })->get();

            $present = $records->where('status', 'present')->count();
            $late = $records->where('status', 'late')->count();
            $absent = $records->where('status', 'absent')->count();

            // Only populate chart if data exists
            if ($present > 0 || $late > 0 || $absent > 0) {
                $doughnutLabels = ['Present', 'Late', 'Absent'];
                $doughnutData = [$present, $late, $absent];
            }
        } else {
            // CONTEXT B: "All Courses" selected -> Show Course Distribution
            $coursesQuery = Course::query();
            if ($user->isLecturer()) {
                $coursesQuery->where('lecturer_id', $user->id);
            }

            $courses = $coursesQuery->with('attendance_sessions.attendance_records')->get();

            foreach ($courses as $course) {
                $count = $course->attendance_sessions->sum(function ($session) {
                    return $session->attendance_records->count();
                });

                // Only show courses that actually have attendance data
                if ($count > 0) {
                    $doughnutLabels[] = $course->course_code;
                    $doughnutData[] = $count;
                }
            }
        }

        $doughnutChartData = [
            'title' => $doughnutTitle,
            'labels' => $doughnutLabels,
            'data' => $doughnutData,
        ];


        // --- 3. Data for "At-Risk" Students ---
        $dangerThreshold = 80;
        $studentsQuery = User::where('role', 'student');

        if ($user->isLecturer()) {
            if ($selectedCourseId) {
                $courseIds = [$selectedCourseId];
            } else {
                $courseIds = Course::where('lecturer_id', $user->id)->pluck('id')->toArray();
            }

            $studentsQuery->whereHas('enrolledCourses', function($q) use ($courseIds) {
                $q->whereIn('courses.id', $courseIds);
            })
            ->with(['enrolledCourses' => function($q) use ($courseIds) {
                $q->whereIn('courses.id', $courseIds)->withCount('attendance_sessions');
            }])
            ->withCount(['attendance_records' => function($q) use ($courseIds) {
                $q->whereHas('attendance_session', function($q2) use ($courseIds) {
                    $q2->whereIn('course_id', $courseIds);
                });
            }]);
        } else {
            $studentsQuery->with(['enrolledCourses' => function($q) {
                $q->withCount('attendance_sessions');
            }])
            ->withCount('attendance_records');
        }

        $leastActiveStudents = $studentsQuery->get()->map(function ($student) {
            $expectedSessions = $student->enrolledCourses->sum('attendance_sessions_count');
            $attendedSessions = $student->attendance_records_count;
            $percentage = $expectedSessions > 0 ? round(($attendedSessions / $expectedSessions) * 100) : 100;

            $student->attendance_percentage = $percentage;
            $student->expected_sessions = $expectedSessions;

            return $student;
        })
        ->filter(function ($student) use ($dangerThreshold) {
            return $student->expected_sessions > 0 && $student->attendance_percentage < $dangerThreshold;
        })
        ->sortBy('attendance_percentage')
        ->take(5)
        ->values();

        // Pass everything to the view
        return view('analytics.dashboard', [
            'attendanceOverTime' => $attendanceOverTime,
            'doughnutChartData' => $doughnutChartData, // <-- Updated Variable
            'leastActiveStudents' => $leastActiveStudents,
            'availableCourses' => $availableCourses ?? collect(),
            'selectedCourseId' => $selectedCourseId,
        ]);
    }
}
