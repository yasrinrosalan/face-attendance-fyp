<?php
// path: laravel_backend/app/Http/Controllers/LecturerController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\AttendanceSession;
use Illuminate\Support\Str; // Import the String helper

class LecturerController extends Controller
{
    public function dashboard()
    {
        $lecturer = Auth::user();
        $courses = $lecturer->courses_lecturer_teaches()->with('attendance_sessions')->get();

        return view('lecturer.dashboard', [
            'lecturer' => $lecturer,
            'courses' => $courses,
        ]);
    }

    /**
     * Create a new attendance session (the "link")
     */
    public function createSession(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'session_title' => 'required|string|max:255',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $lecturer = Auth::user();
        $course = Course::find($request->course_id);

        if ($course->lecturer_id !== $lecturer->id) {
            return back()->with('error', 'You do not own this course.');
        }

        // --- Generate a Unique Referral Code ---
        $code = null;
        do {
            // Generate a simple 6-character uppercase code (e.g., A7B2N9)
            $code = Str::upper(Str::random(6));
            // Check if this code already exists in the table
        } while (AttendanceSession::where('referral_code', $code)->exists());
        // --- End Code Generation ---

        AttendanceSession::create([
            'course_id' => $request->course_id,
            'session_title' => $request->session_title,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'referral_code' => $code, // Save the new code
        ]);

        return redirect('/lecturer/dashboard')->with('success', 'Attendance session created!');
    }

    /**
     * --- NEW METHOD ---
     * Show the details (QR Code, Code) for a specific session.
     * This is the page the lecturer projects in the classroom.
     */
    public function showSession(AttendanceSession $session)
    {
        // Security Check: Does the logged-in lecturer own this course?
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'You do not have permission to view this.');
        }

        // Generate the URL the QR code will point to
        $attendance_url = route('student.attend.form', $session->referral_code);

        return view('lecturer.show_session', [
            'session' => $session,
            'attendance_url' => $attendance_url
        ]);
    }

    public function deleteSession(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return back()->with('error', 'You do not have permission to delete this.');
        }

        $session->delete();
        // Redirect to the show_session page's referrer (the dashboard)
        return redirect()->route('lecturer.dashboard')->with('success', 'Session deleted.');
    }
}
