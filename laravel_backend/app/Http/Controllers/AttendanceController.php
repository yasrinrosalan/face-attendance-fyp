<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success') {
                    $student->face_template_path = "enrolled";
                    $student->save();
                    return response()->json(['success' => true, 'message' => 'Face enrolled successfully!']);
                } else {
                    return response()->json(['success' => false, 'message' => $data['message'] ?? 'Failed to enroll face.'], 500);
                }
            }

            Log::error('Python Enroll Error: ' . $response->body());
            return response()->json(['success' => false, 'message' => 'Failed to enroll face. Server error.'], 500);

        } catch (\Exception $e) {
            Log::error('Python Connection Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not connect to face recognition service.'], 500);
        }
    }

    // --- 2. MARK ATTENDANCE (Fixed: Token + No Location) ---
    public function markAttendance(Request $request)
    {
        // A. Validate Inputs (No lat/long)
        $request->validate([
            'image' => 'required|string',
            'referral_code' => 'required|string|exists:attendance_sessions,referral_code',
            '_token' => 'required|string',
        ]);

        // B. Security Check: One-Time Token
        $submittedToken = $request->input('_token');
        $sessionToken = session('_attendance_token');

        // Check if token matches what's in the session
        if (!$sessionToken || $submittedToken !== $sessionToken) {
            return response()->json(['success' => false, 'message' => 'Invalid session token. Please reload the page.'], 419);
        }

        // NOTE: We do NOT delete the token here. We wait for success.

        // C. Standard Checks
        $student = Auth::user();
        $studentId = $student->id;
        $imageBase64 = $request->input('image');
        $referral_code = $request->input('referral_code');
        $session = AttendanceSession::where('referral_code', $referral_code)->first();

        if (!$session || !$session->isActive()) {
            return response()->json(['success' => false, 'message' => 'Attendance session is not active.']);
        }
        if ($session->attendance_records()->where('student_id', $studentId)->exists()) {
            return response()->json(['success' => true, 'message' => 'You have already attended this session.']);
        }

        // D. Face Verification
        try {
            $response = Http::timeout(30)->post("{$this->pythonServiceUrl}/verify", [
                'image_base64' => $imageBase64,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success' && $data['student_id'] == $studentId) {

                    // Success! Create Record
                    AttendanceRecord::create([
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                        'attended_at' => now(),
                        // No latitude/longitude here
                    ]);

                    // --- IMPORTANT: Delete token ONLY after success ---
                    session()->forget('_attendance_token');
                    // -------------------------------------------------

                    return response()->json(['success' => true, 'message' => 'Attendance marked successfully!']);
                } else {
                    $message = $data['message'] ?? 'Face verification failed. Please try again.';
                    return response()->json(['success' => false, 'message' => $message]);
                }
            }

            Log::error('Python Verify Error: ' . $response->body());
            return response()->json(['success' => false, 'message' => 'Failed to verify face. Server error.'], 500);

        } catch (\Exception $e) {
            Log::error('Python Connection Error: '. $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not connect to face recognition service.'], 500);
        }
    }

    // --- 3. EXPORT ATTENDANCE ---
    public function exportAttendance(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'Unauthorized.');
        }

        $fileName = "attendance_session_{$session->id}.csv";
        $records = $session->attendance_records()->with('student')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No.', 'Student ID', 'Student Name', 'Student Email', 'Attended At (Timestamp)']);

            $counter = 1;

            foreach ($records as $record) {
                fputcsv($file, [
                    $counter++,
                    $record->student->student_id ?? '-',
                    $record->student->name,
                    $record->student->email,
                    $record->attended_at->toDateTimeString(),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
