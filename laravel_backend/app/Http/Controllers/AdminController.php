<?php
// path: laravel_backend/app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash; // <-- ADD THIS

class AdminController extends Controller
{
    public function dashboard(Request $request) // <-- Inject Request
    {
        $users = User::orderBy('created_at', 'desc')
            ->paginate(6, ['*'], 'users_page');

        $sessions = AttendanceSession::with('course', 'course.lecturer', 'attendance_records')
            ->orderBy('starts_at', 'desc')
            ->paginate(6, ['*'], 'sessions_page');

        // --- NEW LOGIC: Handle AJAX Requests ---
        if ($request->ajax()) {
            // If the request has 'users_page', refresh the users table
            if ($request->has('users_page')) {
                return view('admin.partials.users_table', compact('users', 'sessions'))->render();
            }
            // If the request has 'sessions_page', refresh the sessions table
            if ($request->has('sessions_page')) {
                return view('admin.partials.sessions_table', compact('users', 'sessions'))->render();
            }
        }
        // --- END NEW LOGIC ---

        return view('admin.dashboard', compact('users', 'sessions'));
    }

    // --- NEW METHOD: CREATE USER ---
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:lecturer,student',
            // Student ID is required only if role is student
            'student_id' => 'nullable|required_if:role,student|string|unique:users,student_id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'student_id' => $request->student_id,
        ]);

        return back()->with('success', 'New ' . ucfirst($request->role) . ' account created successfully.');
    }
    // --- END NEW METHOD ---

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isStudent() && $user->face_template_path) {
            $this->cleanupFaceData($user);
        }

        $user->delete();
        return back()->with('success', 'User account deleted.');
    }

    public function deleteSession(AttendanceSession $session)
    {
        $session->delete();
        return back()->with('success', 'Attendance session deleted.');
    }

    public function loginAs(User $user)
    {
        session(['admin_impersonating_id' => Auth::id()]);
        Auth::login($user);

        if($user->isLecturer()) return redirect()->route('lecturer.dashboard');
        if($user->isStudent()) return redirect()->route('student.dashboard');

        return redirect('/')->with('success', 'Impersonating ' . $user->name);
    }

    public function deleteEnrollment(User $user)
    {
        if (!$user->isStudent() || !$user->face_template_path) {
            return back()->with('error', 'This user does not have a face enrollment to delete.');
        }

        try {
            $this->cleanupFaceData($user);

            $user->face_template_path = null;
            $user->requesting_face_change = false;
            $user->save();

            return back()->with('success', 'Enrollment reset successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting enrollment: ' . $e->getMessage());
        }
    }

    private function cleanupFaceData(User $user)
    {
        try {
            $pythonServiceUrl = env('PYTHON_SERVICE_URL', 'http://127.0.0.1:5000');

            Http::timeout(5)->post("{$pythonServiceUrl}/delete_enrollment", [
                'student_id' => $user->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to call /delete_enrollment endpoint: " . $e->getMessage());
        }
    }

    // Show all reports
    public function showReports()
    {
        $reports = \App\Models\IssueReport::with('user')
            ->orderBy('status', 'asc') // Pending first
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.reports', compact('reports'));
    }

    // Mark as resolved
    public function resolveReport($id)
    {
        $report = \App\Models\IssueReport::findOrFail($id);
        $report->status = 'resolved';
        $report->save();

        return back()->with('success', 'Issue marked as resolved.');
    }
}