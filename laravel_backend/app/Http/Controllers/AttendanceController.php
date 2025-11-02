<?php
// path: laravel_backend/app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    // --- enrollFace() method is UNCHANGED ---
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
                    $student->face_template_path = $data['path'];
                    $student->save();
                    return response()->json(['success' => true, 'message' => 'Face enrolled successfully!']);
                }
            }

            Log::error('Python Enroll Error: ' . $response->body());
            return response()->json(['success' => false, 'message' => 'Failed to enroll face. Server error.'], 500);

        } catch (\Exception $e) {
            Log::error('Python Connection Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not connect to face recognition service.'], 500);
        }
    }


    /**
     * Handle the face verification POST request.
     * --- MODIFIED ---
     */
    public function markAttendance(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'referral_code' => 'required|string|exists:attendance_sessions,referral_code' // Changed from session_id
        ]);

        $student = Auth::user();
        $studentId = $student->id;
        $imageBase64 = $request->input('image');
        $referral_code = $request->input('referral_code'); // Changed from session_id

        // Find the session by the code
        $session = AttendanceSession::where('referral_code', $referral_code)->first();

        // --- Security Checks ---
        if (!$session || !$session->isActive()) {
            return response()->json(['success' => false, 'message' => 'Attendance session is not active.']);
        }
        if ($session->attendance_records()->where('student_id', $studentId)->exists()) {
            return response()->json(['success' => true, 'message' => 'You have already attended.']);
        }

        try {
            // Send the image to the Python service for verification
            $response = Http::timeout(30)->post("{$this->pythonServiceUrl}/verify", [
                'image_base64' => $imageBase64,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success' && $data['student_id'] == $studentId) {
                    // --- SUCCESS! Face matches the logged-in student. ---

                    AttendanceRecord::create([
                        'attendance_session_id' => $session->id, // Use the found session's ID
                        'student_id' => $studentId,
                        'attended_at' => now(),
                    ]);

                    return response()->json(['success' => true, 'message' => 'Attendance marked successfully!']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Face verification failed. Please try again.']);
                }
            }

            Log::error('Python Verify Error: ' . $response->body());
            return response()->json(['success' => false, 'message' => 'Failed to verify face. Server error.'], 500);

        } catch (\Exception $e) {
            Log::error('Python Connection Error: '. $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not connect to face recognition service.'], 500);
        }
    }

    // --- exportAttendance() method is UNCHANGED ---
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
            fputcsv($file, ['Student ID', 'Student Name', 'Student Email', 'Attended At (Timestamp)']);
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->student->id,
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