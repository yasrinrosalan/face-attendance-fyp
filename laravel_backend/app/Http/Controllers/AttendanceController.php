<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    private $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = env('PYTHON_SERVICE_URL', 'https://ehadir-python.onrender.com');
        // https://ehadir-python.onrender.com
        // http://127.0.0.1:5000
    }

    public function enrollFace(Request $request)
    {
        $request->validate(['image' => 'required|string']);
        $student = Auth::user();

        try {
            // INCREASED TIMEOUT TO 60 SECONDS to allow Render to wake up
            $response = Http::timeout(60)->post("{$this->pythonServiceUrl}/enroll", [
                'student_id' => $student->id,
                'image_base64' => $request->input('image'),
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $student->face_template_path = "enrolled";
                $student->save();
                return response()->json(['success' => true, 'message' => 'Face enrolled successfully!']);
            }

            // THE FIX: Unmask the error! Show EXACTLY what Python/Render rejected.
            $errorDetail = $response->json('message') ?? $response->body() ?? 'No response body';
            return response()->json([
                'success' => false,
                'message' => 'Python rejected: ' . $errorDetail . ' (Status: ' . $response->status() . ')'
            ], 400);

        } catch (\Exception $e) {
            // THE FIX: Unmask Laravel server errors (like Connection Refused)
            return response()->json([
                'success' => false,
                'message' => 'Laravel connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- MARK ATTENDANCE ---
    public function markAttendance(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'encrypted_token' => 'required|string',
            '_token' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // 1. Security Checks
        $sessionToken = session('_attendance_token');
        if (!$sessionToken || $request->input('_token') !== $sessionToken) {
            return response()->json(['success' => false, 'message' => 'Page expired. Reload.'], 419);
        }

        $studentLat = $request->input('latitude');
        $studentLong = $request->input('longitude');

        try {
             $decryptedData = json_decode(Crypt::decryptString($request->input('encrypted_token')), true);
             $sessionId = $decryptedData['session_id'];
             $session = AttendanceSession::findOrFail($sessionId);
        } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Invalid token.'], 400);
        }

        $student = Auth::user();

        // --- NEW: THE ENROLLMENT SECURITY CHECK ---
        $isEnrolled = $student->enrolledCourses()->where('course_id', $session->course_id)->exists();

        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You cannot take attendance because you are not officially enrolled in this course. Please use the Course Code to enroll first.'
            ], 403);
        }
        // ------------------------------------------

        // --- 2. GEOFENCING CHECK (Only for Physical) ---
        if ($session->mode === 'physical') {

            // UPDATED: Dynamically pull the faculty coordinates from the session!
            // We keep the old UMPSA coordinates as a fallback just in case an old session doesn't have it.
            $targetLat = $session->latitude ?? 3.5467;
            $targetLong = $session->longitude ?? 103.4277;

            //2.9036343573297367, 101.84688895304195 rumah eco
            //3.5467586070219017, 103.42774750274906 fkom
            //3.541220151703538, 103.41806436304823 rumah sewa

            $allowedRadius = 200; // 200 meters

            if (is_null($studentLat) || is_null($studentLong)) {
                return response()->json(['success' => false, 'message' => 'Location required for physical class. Allow GPS permission.']);
            }

            // Haversine Formula
            $earthRadius = 6371000;
            $dLat = deg2rad($studentLat - $targetLat);
            $dLon = deg2rad($studentLong - $targetLong);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($targetLat)) * cos(deg2rad($studentLat)) *
                 sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            if ($distance > $allowedRadius) {
                return response()->json([
                    'success' => false,
                    'message' => "Location Error: You are " . round($distance) . "m away from the designated Faculty building. You must be in class."
                ]);
            }
        }

        // 3. Standard Checks
        if (!$session->isActive()) return response()->json(['success' => false, 'message' => 'Session closed.']);
        if ($session->attendance_records()->where('student_id', $student->id)->exists()) {
            return response()->json(['success' => true, 'message' => 'Already attended.']);
        }

        // 4. Face Verification
        try {
            $response = Http::timeout(30)->post("{$this->pythonServiceUrl}/verify", [
                'image_base64' => $request->input('image'),
            ]);

            $data = $response->json();

            if ($response->successful()) {
                if ($data['status'] === 'success' && $data['student_id'] == $student->id) {

                    // Status Logic
                    $status = 'present';
                    if (Schema::hasColumn('attendance_sessions', 'late_tolerance')) {
                         $lateCutoff = $session->starts_at->copy()->addMinutes($session->late_tolerance);
                         if (now()->greaterThan($lateCutoff)) { $status = 'late'; }
                    }

                    // Save Record
                    AttendanceRecord::create([
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'attended_at' => now(),
                        'status' => $status,
                        'latitude' => $studentLat,
                        'longitude' => $studentLong,
                    ]);

                    \Illuminate\Support\Facades\Cache::forget("lecturer.dashboard.{$session->course->lecturer_id}");
                    session()->forget('_attendance_token');

                    $msg = ($status == 'late') ? 'Attendance marked (LATE).' : 'Attendance marked successfully!';
                    return response()->json(['success' => true, 'message' => $msg]);
                } else {
                    return response()->json(['success' => false, 'message' => $data['message'] ?? 'Face mismatch.']);
                }
            }
            return response()->json(['success' => false, 'message' => $data['message'] ?? 'Server Error.'], 500);

        } catch (\Exception $e) {
            // Modified to show actual error for debugging
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function exportAttendance(AttendanceSession $session)
    {
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'Unauthorized.');
        }
        $fileName = "attendance_{$session->id}.csv";
        $records = $session->attendance_records()->with('student')->get();
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No.', 'Student ID', 'Name', 'Email', 'Time', 'Status', 'Location (Lat/Long)']);
            $counter = 1;
            foreach ($records as $record) {
                fputcsv($file, [
                    $counter++, $record->student->student_id, $record->student->name, $record->student->email,
                    $record->attended_at, strtoupper($record->status),
                    "{$record->latitude}, {$record->longitude}"
                ]);
            }
            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }
}
