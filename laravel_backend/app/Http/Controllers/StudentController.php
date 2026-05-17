<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Str;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\AttendanceRecord; // Ensure this is imported
use Carbon\Carbon; // Import Carbon for dates

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = Auth::user();

        // 1. Find courses the student has attended
        $coursesIds = AttendanceSession::whereHas('attendance_records', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })->pluck('course_id')->unique();

        $courses = Course::whereIn('id', $coursesIds)->with(['attendance_sessions.attendance_records' => function($query) use ($student) {
        $query->where('student_id', $student->id);
        }])->get();

        // 2. Calculate statistics
        $courseStats = $courses->map(function ($course) use ($student) {
            $totalSessions = $course->attendance_sessions->count();
            $attendedSessions = $course->attendance_sessions->filter(function($session) {
                 return $session->attendance_records->isNotEmpty();
            })->count();

            $percentage = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100) : 0;

            $statusColor = 'success';
            if ($percentage < 75) { $statusColor = 'danger'; }
            elseif ($percentage < 85) { $statusColor = 'warning'; }

            return (object) [
                'course_code' => $course->course_code,
                'course_name' => $course->course_name,
                'total_sessions' => $totalSessions,
                'attended_sessions' => $attendedSessions,
                'percentage' => $percentage,
                'status_color' => $statusColor,
            ];
        });

        // --- 3. CALENDAR LOGIC (The missing part) ---
        $today = now();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        // Get attendance for this month to show on calendar
        $monthlyRecords = AttendanceRecord::where('student_id', $student->id)
            ->whereBetween('attended_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function($item) {
                return $item->attended_at->format('Y-m-d');
            });

        $days = [];
        // Add empty slots for days before the 1st of the month (alignment)
        // dayOfWeek returns 0 (Sunday) to 6 (Saturday)
        $startDayOfWeek = $startOfMonth->dayOfWeek;
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $days[] = null;
        }

        // Add actual days
        for ($day = 1; $day <= $endOfMonth->day; $day++) {
            $currentDate = $startOfMonth->copy()->addDays($day - 1);
            $dateString = $currentDate->format('Y-m-d');

            $status = null;
            if ($monthlyRecords->has($dateString)) {
                $status = $monthlyRecords[$dateString]->status; // 'present' or 'late'
            }

            $days[] = (object) [
                'date' => $day,
                'status' => $status,
                'is_today' => $currentDate->isToday(),
            ];
        }
        // --------------------------------------------

        return view('student.dashboard', [
            'student' => $student,
            'courseStats' => $courseStats,
            'days' => $days,   // <--- Now passing $days
            'today' => $today, // <--- Now passing $today
        ]);
    }

    public function showEnrollmentPage()
    {
        $student = Auth::user();
        return view('student.enrollment', ['student' => $student]);
    }

    public function showEnrollForm()
    {
        return view('student.enroll_face');
    }

    public function requestFaceChange(Request $request)
    {
        return back()->with('success', 'Request submitted to admin.');
    }

    public function findSession(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:6',
        ]);

        $session = AttendanceSession::where('referral_code', Str::upper($request->referral_code))->first();

        if (!$session) {
            return back()->with('error', 'Session not found or code invalid.');
        }

        if (!$session->isActive()) {
             return back()->with('error', 'This session has expired.');
        }

        // Create fallback token for manual entry
        $data = [ 'session_id' => $session->id, 'expires_at' => now()->addSeconds(60)->timestamp ];
        $encryptedToken = Crypt::encryptString(json_encode($data));

        return redirect()->route('student.attend.form', $encryptedToken);
    }

    public function showAttendForm($token)
    {
        try {
             $decryptedData = json_decode(Crypt::decryptString($token), true);
             $sessionId = $decryptedData['session_id'];
             $expiresAtTimestamp = $decryptedData['expires_at'];

             if (now()->timestamp > $expiresAtTimestamp) {
                 return redirect()->route('student.dashboard')->with('error', 'QR code expired.');
             }

             $session = AttendanceSession::findOrFail($sessionId);

             if (!$session->isActive()) {
                return redirect()->route('student.dashboard')->with('error', 'Session inactive.');
             }

             $formToken = Str::random(40);
             session(['_attendance_token' => $formToken]);

             return view('student.attend_form', [
                'session' => $session,
                'formToken' => $formToken,
                'encryptedToken' => $token
            ]);

        } catch (\Exception $e) {
             return redirect()->route('student.dashboard')->with('error', 'Invalid link.');
        }
    }

    public function enrollCourse(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'course_code' => 'required|string'
        ]);

        // 1. Find the course by the code the student typed
        $course = \App\Models\Course::where('course_code', $request->course_code)->first();

        if (!$course) {
            return back()->with('error', 'Course not found. Please check the code and try again.');
        }

        $student = \Illuminate\Support\Facades\Auth::user();

        // 2. Check if the student is already enrolled to prevent duplicates
        if ($student->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return back()->with('error', 'You are already enrolled in ' . $course->course_name . '.');
        }

        // 3. Automatically insert the record into the course_student table!
        $student->enrolledCourses()->attach($course->id);

        return back()->with('success', 'Success! You are now enrolled in ' . $course->course_name . '.');
    }

    public function scanner()
{
    return view('student.scanner');
}
}