<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Str;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = Auth::user();

        // 1. Fetch courses the student is officially enrolled in
        $courses = $student->enrolledCourses()->with(['attendance_sessions.attendance_records' => function($query) use ($student) {
            $query->where('student_id', $student->id);
        }])->get();

        // --- NEW FEATURE: CHECK FOR ACTIVE LIVE SESSION ---
        $activeSession = AttendanceSession::whereIn('course_id', $student->enrolledCourses->pluck('id'))
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with('course')
            ->orderBy('ends_at', 'asc') // If multiple, get the one ending soonest
            ->first();
        // ---------------------------------------------------

        // 2. Calculate statistics
        $courseStats = $courses->map(function ($course) use ($student) {
            $totalSessions = $course->attendance_sessions->count();

            // Count how many sessions this specific student has an attendance record for
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

        // --- 3. CALENDAR LOGIC ---
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

        return view('student.dashboard', [
            'student' => $student,
            'courseStats' => $courseStats,
            'days' => $days,
            'today' => $today,
            'activeSession' => $activeSession, // <-- Pass active session to the view
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

    // --- FIX APPLIED HERE: Actually update the database ---
    public function requestFaceChange(Request $request)
    {
        $student = Auth::user();

        if (!$student->face_template_path) {
            return back()->with('error', 'You are not enrolled yet.');
        }

        if ($student->requesting_face_change) {
            // Updated text to reference the lecturer instead of admin
            return back()->with('info', 'You have already requested a face data reset. Please wait for your lecturer\'s approval.');
        }

        // Set the boolean to true and save it to the database
        $student->requesting_face_change = true;
        $student->save();

        // Updated text to reference the lecturer instead of admin
        return back()->with('success', 'Request sent to your lecturer. You will be able to re-enroll once approved.');
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