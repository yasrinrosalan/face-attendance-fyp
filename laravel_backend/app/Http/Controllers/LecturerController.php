<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Course;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PDF;

class LecturerController extends Controller
{
    public function dashboard()
    {
        $lecturer = Auth::user();

        // --- ADDED: SEMESTER FILTERING LOGIC ---
        // In a real production app, these could be fetched from a 'Settings' table.
        // For now, we set the active semester statically to filter the dashboard.
        $currentYear = '2025/2026';
        $currentSemester = 1;

        // Fetch courses for the current semester with latest session and its records
        $courses = $lecturer->courses_lecturer_teaches()
            ->where('academic_year', $currentYear)
            ->where('semester', $currentSemester)
            ->withCount('attendance_sessions')
            ->get();
        // ----------------------------------------

        // Calculate stats for the latest session of each course
        $coursesWithStats = $courses->map(function ($course) {
            $latestSession = $course->attendance_sessions->first();
            $stats = null;

            if ($latestSession) {
                // Explicitly load records for stats calculation
                $latestSession->load('attendance_records');

                // Using the actual enrolled student count via the pivot table relationship
                $totalStudents = $course->students()->count();

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
            'courses' => $courses,
        ]);
    }

    public function createCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:20',
            'academic_year' => 'required|string|max:20', // e.g., "2025/2026"
            'semester' => 'required|integer|min:1|max:3', // e.g., 1 or 2
        ]);

        Course::create([
            'course_name' => $request->course_name,
            'course_code' => $request->course_code,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
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
        // --- UPDATED: ADDED LOCATION VALIDATION ---
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'session_title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'mode' => 'required|in:physical,online',
            'week_number' => 'required|integer|min:1|max:14',
            'location_coords' => 'required_if:mode,physical', // Requires dropdown if mode is physical
        ]);

        $lecturer = Auth::user();
        $course = Course::find($request->course_id);

        if ($course->lecturer_id !== $lecturer->id) {
            return back()->with('error', 'You do not own this course.');
        }

        // --- NEW: EXTRACT LAT/LONG FROM DROPDOWN ---
        $latitude = null;
        $longitude = null;

        if ($request->mode === 'physical' && $request->filled('location_coords')) {
            $coords = explode(',', $request->location_coords);
            if (count($coords) === 2) {
                $latitude = trim($coords[0]);
                $longitude = trim($coords[1]);
            }
        }
        // -------------------------------------------

        $code = Str::upper(Str::random(6));
        $startsAt = now();
        $endsAt = $startsAt->copy()->addMinutes($request->duration);

        // --- UPDATED: SAVE LAT/LONG TO DATABASE ---
        AttendanceSession::create([
            'course_id' => $request->course_id,
            'session_title' => $request->session_title,
            'week_number' => $request->week_number,
            'mode' => $request->mode,
            'latitude' => $latitude,     // Saves the extracted latitude
            'longitude' => $longitude,   // Saves the extracted longitude
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

        // 1. Fetch all officially enrolled students for this course
        $enrolledStudents = $session->course->students()->orderBy('name')->get();

        // 2. Fetch attendance records for THIS session, keyed by student_id for easy lookup
        $attendanceRecords = $session->attendance_records->keyBy('student_id');

        // 3. Merge them together to figure out who is absent
        $attendanceData = $enrolledStudents->map(function ($student) use ($attendanceRecords) {
            $record = $attendanceRecords->get($student->id);

            return (object) [
                'student' => $student,
                'status' => $record ? $record->status : 'absent',
                'attended_at' => $record ? $record->attended_at : null,
            ];
        });

        // 4. Calculate quick stats for the dashboard
        $totalStudents = $enrolledStudents->count();
        $presentCount = $attendanceData->where('status', 'present')->count();
        $lateCount = $attendanceData->where('status', 'late')->count();
        $absentCount = $attendanceData->where('status', 'absent')->count();

        return view('lecturer.show_session', [
            'session' => $session,
            'attendanceData' => $attendanceData,
            'totalStudents' => $totalStudents,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'absentCount' => $absentCount,
        ]);
    }

    public function getDynamicQrData(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id() || !$session->isActive()) {
             return response()->json(['error' => 'Unauthorized or expired'], 403);
        }

        $data = [
            'session_id' => $session->id,
            'expires_at' => now()->addSeconds(15)->timestamp,
        ];

        $encryptedToken = Crypt::encryptString(json_encode($data));
        $url = route('student.attend.form', ['token' => $encryptedToken]);

        return response()->json([
            'qr_url' => $url
        ]);
    }

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

        if (!$student->enrolledCourses()->where('course_id', $session->course_id)->exists()) {
            $student->enrolledCourses()->attach($session->course_id);
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

    public function exportCourseCsv($course_id)
    {
        $course = Course::findOrFail($course_id);

        if ($course->lecturer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Fetch students via the course_student pivot table
        $students = $course->students()->orderBy('name')->get();

        // Fetch all sessions for this course, ordered sequentially by week and time
        $sessions = AttendanceSession::where('course_id', $course_id)
                                     ->orderBy('week_number')
                                     ->orderBy('starts_at')
                                     ->get();

        $fileName = $course->course_code . '_Semester_' . $course->semester . '_Report.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($students, $sessions) {
            $file = fopen('php://output', 'w');

            // 1. Create the Header Row
            $headerRow = ['Student Name', 'Student ID'];
            foreach ($sessions as $session) {
                // Generates headers like: "Wk 1 (12/04)"
                $headerRow[] = 'Wk ' . $session->week_number . ' (' . Carbon::parse($session->starts_at)->format('d/m') . ')';
            }
            $headerRow[] = 'Total Present (%)';
            fputcsv($file, $headerRow);

            // 2. Loop through each student to construct their attendance row
            foreach ($students as $student) {
                $row = [$student->name, $student->student_id];
                $presentCount = 0;

                foreach ($sessions as $session) {
                    $record = AttendanceRecord::where('student_id', $student->id)
                                              ->where('attendance_session_id', $session->id)
                                              ->first();

                    if ($record && in_array($record->status, ['present', 'late'])) {
                        $row[] = '1'; // Marked as attended
                        $presentCount++;
                    } else {
                        $row[] = '0'; // Absent
                    }
                }

                // Calculate the final percentage across the whole semester
                $percentage = count($sessions) > 0 ? round(($presentCount / count($sessions)) * 100) : 0;
                $row[] = $percentage . '%';

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
