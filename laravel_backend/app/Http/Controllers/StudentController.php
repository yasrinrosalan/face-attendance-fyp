<?php
// path: laravel_backend/app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Str; // <-- ADD THIS IMPORT for generating the token

class StudentController extends Controller
{
    /**
     * Show the student's main page.
     */
    public function dashboard()
    {
        $student = Auth::user();

        // --- 1. GET ATTENDANCE STATISTICS ---
        $records = $student->attendance_records()
                          ->with('attendance_session.course')
                          ->orderBy('attended_at', 'desc')
                          ->get();

        $groupedRecords = $records->groupBy('attendance_session.course.course_name');
        $totalAttended = $records->count();

        // --- 2. PREPARE DATA FOR CALENDAR ---
        $attendedDates = $records->pluck('attended_at')->map(function ($date) {
            return $date->format('Y-m-d');
        })->flip();

        $today = Carbon::today();
        $calendarStartDate = $today->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $calendarEndDate = $today->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $days = new DatePeriod(
            $calendarStartDate,
            new DateInterval('P1D'),
            $calendarEndDate->addDay()
        );

        return view('student.dashboard', [
            'student' => $student,
            'groupedRecords' => $groupedRecords,
            'totalAttended' => $totalAttended,
            'today' => $today,
            'days' => $days,
            'attendedDates' => $attendedDates,
        ]);
    }

    /**
     * Show the student's enrollment management page.
     */
    public function showEnrollmentPage()
    {
        $student = Auth::user();
        return view('student.enrollment', compact('student'));
    }

    /**
     * Show the face enrollment page.
     */
    public function showEnrollForm()
    {
        if (Auth::user()->face_template_path) {
            return redirect()->route('student.enrollment.page')
                ->with('error', 'You have already enrolled your face.');
        }
        return view('student.enroll');
    }

    /**
     * Allow a student to request a change to their enrollment.
     */
    public function requestFaceChange()
    {
        $student = Auth::user();

        if ($student->face_template_path && !$student->requesting_face_change) {
            $student->requesting_face_change = true;
            $student->save();
            return back()->with('success', 'Your request to change your enrollment has been sent to the administrator.');
        }
        return back()->with('error', 'You cannot make this request right now.');
    }

    /**
     * Handle the form submission for entering a referral code.
     */
    public function findSession(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|exists:attendance_sessions,referral_code'
        ], [
            'referral_code.exists' => 'The provided attendance code is invalid.'
        ]);

        $code = $request->input('referral_code');
        return redirect()->route('student.attend.form', ['referral_code' => $code]);
    }

    /**
     * Show the page to mark attendance.
     * --- MODIFIED ---
     */
    public function showAttendForm(string $referral_code)
    {
        $student = Auth::user();
        $session = AttendanceSession::where('referral_code', $referral_code)->first();

        if (!$session) {
            abort(404, 'Session not found.');
        }
        if ($student->face_template_path == null) {
            return redirect('/student/dashboard')->with('error', 'You must enroll your face before you can attend.');
        }
        if (!$session->isActive()) {
            return redirect('/student/dashboard')->with('error', 'That attendance session is not active.');
        }
        $hasAttended = $session->attendance_records()->where('student_id', $student->id)->exists();
        if ($hasAttended) {
             return redirect('/student/dashboard')->with('success', 'You have already attended this session.');
        }

        // --- ADDED THIS BLOCK ---
        // 1. Generate a secure, random token
        $token = Str::random(40);
        // 2. Store this token in the user's session
        session(['_attendance_token' => $token]);
        // --- END ADDED BLOCK ---

        return view('student.attend', [
            'session' => $session,
            'attendance_token' => $token // 3. Pass the token to the view
        ]);
    }
}