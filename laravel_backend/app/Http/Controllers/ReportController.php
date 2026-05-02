<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IssueReport;
use Illuminate\Support\Facades\Auth;

// --- ADDED IMPORTS FOR ATTENDANCE EXPORT ---
use App\Models\Course;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
// -------------------------------------------

class ReportController extends Controller
{
    // Show the form
    public function create()
    {
        return view('reports.create');
    }

    // Save the report
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        IssueReport::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        // Redirect back to their specific dashboard
        $route = Auth::user()->isLecturer() ? 'lecturer.dashboard' : 'student.dashboard';

        return redirect()->route($route)->with('success', 'Issue reported successfully. Admin will review it.');
    }

    // --- NEW FUNCTION: 14-WEEK END OF SEMESTER CSV REPORT ---
    // app/Http/Controllers/ReportController.php

public function exportCourseCsv($id)
{
    // Ensure we load students and their sessions
    $course = Course::with(['students'])->findOrFail($id);

    // Sort sessions by week number for the columns
    $sessions = AttendanceSession::where('course_id', $id)
        ->orderBy('week_number')
        ->orderBy('starts_at')
        ->get();

    $callback = function() use ($course, $sessions) {
        $file = fopen('php://output', 'w');

        // 1. Generate Headers
        $header = ['Student Name', 'Student ID'];
        foreach ($sessions as $session) {
            $header[] = "Wk {$session->week_number} (" . $session->starts_at->format('d/m') . ")";
        }
        $header[] = 'Total Present (%)';
        fputcsv($file, $header);

        // 2. Generate Student Rows
        foreach ($course->students as $student) {
            $row = [
                $student->name,       // Real Name
                $student->student_id, // Matrix ID
            ];

            $presentCount = 0;

            foreach ($sessions as $session) {
                // --- THE CRITICAL FIX IS HERE ---
                // We must use 'student_id' (the FK in your migration), NOT 'user_id'
                $attended = AttendanceRecord::where('attendance_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['present', 'late']) // Count both as present
                    ->exists();

                $row[] = $attended ? '1' : '0';
                if ($attended) $presentCount++;
            }

            // Calculate Percentage
            $totalSessions = $sessions->count();
            $percentage = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 2) : 0;
            $row[] = $percentage . '%';

            fputcsv($file, $row);
        }

        fclose($file);
    };

    $fileName = str_replace(' ', '_', $course->course_code) . "_14_Week_Report.csv";
    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    return response()->stream($callback, 200, $headers);
}
    // ---------------------------------------------------------
}
