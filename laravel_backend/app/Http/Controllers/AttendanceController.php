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
        $this->pythonServiceUrl = env('PYTHON_SERVICE_URL', 'http://127.0.0.1:5000');
    }

    public function enrollFace(Request $request)
    {
        // (Keep existing enroll logic unchanged)
        $request->validate(['image' => 'required|string']);
        $student = Auth::user();
        try {
            $response = Http::timeout(30)->post("{$this->pythonServiceUrl}/enroll", [
                'student_id' => $student->id, 'image_base64' => $request->input('image'),
            ]);
            if ($response->successful() && $response->json('status') === 'success') {
                $student->face_template_path = "enrolled"; $student->save();
                return response()->json(['success' => true, 'message' => 'Face enrolled successfully!']);
            }
            return response()->json(['success' => false, 'message' => $response->json('message') ?? 'Failed.'], 400);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => 'Server Error.'], 500); }
    }

    // --- MARK ATTENDANCE WITH GEOFENCING ---
    public function markAttendance(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'encrypted_token' => 'required|string',
            '_token' => 'required|string',
            'latitude' => 'required|numeric',  // <--- NEW REQUIREMENT
            'longitude' => 'required|numeric', // <--- NEW REQUIREMENT
        ]);

        // 1. Security Checks
        $sessionToken = session('_attendance_token');
        if (!$sessionToken || $request->input('_token') !== $sessionToken) {
            return response()->json(['success' => false, 'message' => 'Page expired. Reload.'], 419);
        }

        try {
             $decryptedData = json_decode(Crypt::decryptString($request->input('encrypted_token')), true);
             $sessionId = $decryptedData['session_id'];
             $session = AttendanceSession::findOrFail($sessionId);
        } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Invalid token.'], 400);
        }

        // --- 2. GEOFENCING CHECK ---

        // SET YOUR TARGET: Example (UMPSA Pekan Library)
        $targetLat = 2.9036;     // <--- CHANGE THIS TO YOUR CLASSROOM
        $targetLong = 101.8468;  // <--- CHANGE THIS TO YOUR CLASSROOM
        $allowedRadius = 200;    // Allowed range in meters (e.g., 200m)

        //2.9036343573297367, 101.84688895304195 rumah eco
        //3.5467586070219017, 103.42774750274906 fkom

        $studentLat = $request->input('latitude');
        $studentLong = $request->input('longitude');

        // Calculate Distance (Haversine Formula)
        $earthRadius = 6371000;
        $dLat = deg2rad($studentLat - $targetLat);
        $dLon = deg2rad($studentLong - $targetLong);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($targetLat)) * cos(deg2rad($studentLat)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        // Reject if too far
        if ($distance > $allowedRadius) {
            return response()->json([
                'success' => false,
                'message' => "Location Error: You are " . round($distance) . "m away. You must be in class."
            ]);
        }
        // ---------------------------

        // 3. Standard Checks
        $student = Auth::user();
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

                    // Save Record WITH Location Data
                    AttendanceRecord::create([
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'attended_at' => now(),
                        'status' => $status,
                        'latitude' => $studentLat,   // <--- Save
                        'longitude' => $studentLong, // <--- Save
                    ]);

                    \Illuminate\Support\Facades\Cache::forget("lecturer.dashboard.{$session->course->lecturer_id}");
                    session()->forget('_attendance_token');

                    $msg = ($status == 'late') ? 'Attendance marked (LATE).' : 'Attendance marked successfully!';
                    return response()->json(['success' => true, 'message' => $msg]);
                } else {
                    return response()->json(['success' => false, 'message' => $data['message'] ?? 'Face mismatch.']);
                }
            }
            return response()->json(['success' => false, 'message' => 'Server Error.'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service connection failed.'], 500);
        }
    }

    public function exportAttendance(AttendanceSession $session)
    {
        // (Keep your existing export code)
        // ... (omitted for brevity, copy from previous version)
        if ($session->course->lecturer_id !== Auth::id()) {
            return redirect('/lecturer/dashboard')->with('error', 'Unauthorized.');
        }
        $fileName = "attendance_{$session->id}.csv";
        $records = $session->attendance_records()->with('student')->get();
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No.', 'Student ID', 'Name', 'Email', 'Time', 'Status', 'Location (Lat/Long)']); // Added Header
            $counter = 1;
            foreach ($records as $record) {
                fputcsv($file, [
                    $counter++, $record->student->student_id, $record->student->name, $record->student->email,
                    $record->attended_at, strtoupper($record->status),
                    "{$record->latitude}, {$record->longitude}" // Added Data
                ]);
            }
            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }
}