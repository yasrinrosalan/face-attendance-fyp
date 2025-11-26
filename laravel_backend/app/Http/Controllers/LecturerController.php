<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\AttendanceSession;
use Illuminate\Support\Str;
use PDF; // Import the PDF library

class LecturerController extends Controller
{
    public function dashboard()
    {
        $lecturer = Auth::user();

        // 1. Fetch courses and ONLY the latest session basic info (removed nested loading here)
        $courses = $lecturer->courses_lecturer_teaches()
            ->with(['attendance_sessions' => function ($query) {
                $query->latest('starts_at')->take(1);
            }])
            ->get();

        // 2. Calculate stats based on the latest session found
        $coursesWithStats = $courses->map(function ($course) {
            // Get the session object
            $latestSession = $course->attendance_sessions->first();
            $stats = null;

            if ($latestSession) {
                // --- THE FIX IS HERE ---
                // Explicitly load the records for this specific session instance.
                // This ensures the relationship data is actually fetched.
                $latestSession->load('attendance_records');
                // -----------------------

                // TODO: Replace placeholder with actual enrolled student count
                $totalStudents = 50;

                // Now count the loaded records
                $presentCount = $latestSession->attendance_records->where('status', 'present')->count();
                $lateCount = $latestSession->attendance_records->where('status', 'late')->count();

                $attendanceRate = $totalStudents > 0 ? round((($presentCount + $lateCount) / $totalStudents) * 100) : 0;

                $stats = (object) [
                    'session_title' => $latestSession->session_title,
                    'attendance_rate' => $attendanceRate,
                    'present_count' => $presentCount,
                    'late_count' => $lateCount,
                ];
            }

            $course->latest_session_stats = $stats;
            return $course;
        });

        return view('lecturer.dashboard', [
            'lecturer' => $lecturer,
            'courses' => $coursesWithStats,
        ]);
    }

    public function createCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:20',
        ]);

        Course::create([
            'course_name' => $request->course_name,
            'course_code' => $request->course_code,
            'lecturer_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'New course added successfully!');
    }

    public function showCourse(\App\Models\Course $course)
    {
        if ($course->lecturer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $course->load(['attendance_sessions' => function($query) {
            $query->orderByDesc('starts_at');
        }]);

        return view('lecturer.course.show', compact('course'));
    }

    public function createSession(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'session_title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
        ]);

        $lecturer = Auth::user();
        $course = Course::find($request->course_id);

        if ($course->lecturer_id !== $lecturer->id) {
            return back()->with('error', 'You do not own this course.');
        }

        $code = null;
        do {
            $code = Str::upper(Str::random(6));
        } while (AttendanceSession::where('referral_code', $code)->exists());

        $startsAt = now();
        $endsAt = $startsAt->copy()->addMinutes($request->duration);

        AttendanceSession::create([
            'course_id' => $request->course_id,
            'session_title' => $request->session_title,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'referral_code' => $code,
        ]);

        return redirect('/lecturer/dashboard')->with('success', 'Attendance session created!');
    }

    public function deleteSession(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return back()->with('error', 'You do not have permission to delete this.');
        }
        $session->delete();
        return redirect()->route('lecturer.dashboard')->with('success', 'Session deleted.');
    }

    public function showSession(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'You do not have permission to view this.');
        }

        $attendance_url = route('student.attend.form', $session->referral_code);

        return view('lecturer.show_session', [
            'session' => $session,
            'attendance_url' => $attendance_url
        ]);
    }

    // --- NEW FUNCTION: MANUAL OVERRIDE ---
    public function manualAttendance(Request $request, AttendanceSession $session)
    {
        $request->validate([
            'student_email' => 'required|email|exists:users,email',
        ], [
            'student_email.exists' => 'This student email is not registered.',
        ]);

        if ($session->course->lecturer_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $student = \App\Models\User::where('email', $request->student_email)
                    ->where('role', 'student')
                    ->first();

        if (!$student) {
            return back()->with('error', 'User found but is not a student role.');
        }

        if ($session->attendance_records()->where('student_id', $student->id)->exists()) {
            return back()->with('error', "Student '{$student->name}' is already present.");
        }

        \App\Models\AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'attended_at' => now(),
            'status' => 'present'
        ]);

        return back()->with('success', "Success: Manually marked '{$student->name}' as present.");
    }

    // --- NEW FUNCTION: DOWNLOAD PDF ---
    public function downloadPdf(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'Unauthorized.');
        }

        $records = $session->attendance_records()->with('student')->get();
        $lecturer = Auth::user();

        $pdf = PDF::loadView('exports.attendance_pdf', [
            'session' => $session,
            'records' => $records,
            'lecturer' => $lecturer,
            'generated_at' => now(),
        ]);

        $fileName = "attendance_{$session->course->course_code}_{$session->starts_at->format('Y-m-d')}.pdf";
        return $pdf->download($fileName);
    }
}
