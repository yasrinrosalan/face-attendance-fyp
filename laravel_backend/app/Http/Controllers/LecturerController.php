<?php
// path: laravel_backend/app/Http/Controllers/LecturerController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\AttendanceSession;

class LecturerController extends Controller
{
    public function dashboard()
    {
        $lecturer = Auth::user();
        // This ALREADY filters courses by the logged-in lecturer
        $courses = $lecturer->courses_lecturer_teaches()->with('attendance_sessions')->get();

        return view('lecturer.dashboard', [
            'lecturer' => $lecturer,
            'courses' => $courses,
        ]);
    }

    // --- NEW METHOD: CREATE COURSE ---
    public function createCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:20', // Removed 'unique' to allow different lecturers to teach same subject code if needed, or keep unique if strict.
        ]);

        Course::create([
            'course_name' => $request->course_name,
            'course_code' => $request->course_code,
            'lecturer_id' => Auth::id(), // <--- THIS BINDS IT TO THE ACCOUNT
        ]);

        return redirect()->back()->with('success', 'New course added successfully!');
    }
    // --- END NEW METHOD ---

    // ... (createSession and deleteSession methods remain unchanged) ...
    public function createSession(Request $request)
    {
        // ... existing code ...
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

        // ... generate code ...
        $code = null;
        do {
            $code = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
        } while (AttendanceSession::where('referral_code', $code)->exists());

        AttendanceSession::create([
            'course_id' => $request->course_id,
            'session_title' => $request->session_title,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
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

    // ... (showSession method unchanged) ...
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
}
