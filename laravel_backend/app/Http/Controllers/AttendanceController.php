<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema; // Added for checking columns
use App\Models\User;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    private $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = env('PYTHON_SERVICE_URL', 'http://127.0.0.1:5000');
    }

    // --- 1. ENROLL FACE ---
    public function enrollFace(Request $request)
    {
        $request->validate(['image' => 'required|string']);

        $student = Auth::user();
        $studentId = $student->id;
        $imageBase64 = $request->input('image');

        try {
            $response = Http::timeout(30)->post("{$this->pythonServiceUrl}/enroll", [
                'student_id' => $studentId,
                'image_base64' => $imageBase64,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                if (isset($data['status']) && $data['status'] === 'success') {
                    $student->face_template_path = "enrolled";
                    $student->save();
                    return response()->json(['success' => true, 'message' => 'Face enrolled successfully!']);
                } else {
                    return response()->json(['success' => false, 'message' => $data['message'] ?? 'Failed.'], 400);
                }
            }
            return response()->json(['success' => false, 'message' => 'Server Error.'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not connect to face service.'], 500);
        }
    }

    // --- 2. MARK ATTENDANCE (FIXED: Removed Expiry Check on Submission) ---
    public function markAttendance(Request $request)
    {
        // A. Validate Inputs
        $request->validate([
            'image' => 'required|string',
            'encrypted_token' => 'required|string',
            '_token' => 'required|string',
        ]);

        // B. Security Check: One-Time Form Token (Prevents double submission)
        $submittedToken = $request->input('_token');
        $sessionToken = session('_attendance_token');

        if (!$sessionToken || $submittedToken !== $sessionToken) {
            return response()->json(['success' => false, 'message' => 'Page expired. Please scan the QR code again.'], 419);
        }

        // --- DECRYPTION ONLY (Time Check Removed) ---
        try {
             // We decrypt ONLY to get the Session ID.
             // We intentionally REMOVED the timestamp check here.
             // As long as they passed the "Entry Gate" (scanning the QR),
             // they are allowed to take their time to position their face.
             $decryptedData = json_decode(Crypt::decryptString($request->input('encrypted_token')), true);
             $sessionId = $decryptedData['session_id'];

             $session = AttendanceSession::findOrFail($sessionId);

        } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Invalid security token.'], 400);
        }
        // ----------------------------------------------------

        // C. Standard Checks (Is the CLASS still running?)
        $student = Auth::user();
        $studentId = $student->id;
        $imageBase64 = $request->input('image');

        // We check if the Class Session (e.g. 1 hour) is still active.
        if (!$session->isActive()) {
            return response()->json(['success' => false, 'message' => 'The attendance session has ended.']);
        }
        if ($session->attendance_records()->where('student_id', $studentId)->exists()) {
            return response()->json(['success' => true, 'message' => 'You have already attended this session.']);
        }

        // D. Face Verification
        try {
            $response = Http::timeout(30)->post("{$this->pythonServiceUrl}/verify", [
                'image_base64' => $imageBase64,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                if ($data['status'] === 'success' && $data['student_id'] == $studentId) {

                    // Determine Status (Present/Late)
                    $status = 'present';
                    // Check if the 'late_tolerance' column exists to prevent errors
                    if (Schema::hasColumn('attendance_sessions', 'late_tolerance')) {
                         $lateCutoff = $session->starts_at->copy()->addMinutes($session->late_tolerance);
                         if (now()->greaterThan($lateCutoff)) { $status = 'late'; }
                    }

                    // Create Record
                    AttendanceRecord::create([
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                        'attended_at' => now(),
                        'status' => $status,
                    ]);

                    // Clear Caches & Tokens
                    \Illuminate\Support\Facades\Cache::forget("lecturer.dashboard.{$session->course->lecturer_id}");
                    session()->forget('_attendance_token');

                    $msg = ($status == 'late') ? 'Attendance marked (LATE).' : 'Attendance marked successfully!';
                    return response()->json(['success' => true, 'message' => $msg]);
                } else {
                    return response()->json(['success' => false, 'message' => $data['message'] ?? 'Face verification failed.']);
                }
            }
            return response()->json(['success' => false, 'message' => $data['message'] ?? 'Server error.'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not connect to face recognition service.'], 500);
        }
    }

    public function exportAttendance(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'Unauthorized.');
        }

        $fileName = "attendance_session_{$session->id}.csv";
        $records = $session->attendance_records()->with('student')->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No.', 'Student ID', 'Student Name', 'Student Email', 'Attended At', 'Status']);
            $counter = 1;
            foreach ($records as $record) {
                fputcsv($file, [
                    $counter++,
                    $record->student->student_id ?? '-',
                    $record->student->name,
                    $record->student->email,
                    $record->attended_at->toDateTimeString(),
                    strtoupper($record->status),
                ]);
            }
            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }
}