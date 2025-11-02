<?php
// path: laravel_backend/app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Show the main admin dashboard with all users and sessions.
     */
    public function dashboard()
    {
        $users = User::orderBy('requesting_face_change', 'desc') // <-- Show requests at the top
            ->orderBy('role')->orderBy('name')
            ->paginate(15, ['*'], 'users_page');

        $sessions = AttendanceSession::with('course', 'course.lecturer', 'attendance_records')
            ->orderBy('starts_at', 'desc')
            ->paginate(15, ['*'], 'sessions_page');

        return view('admin.dashboard', compact('users', 'sessions'));
    }

    /**
     * Delete a user account (student or lecturer).
     */
    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isStudent() && $user->face_template_path) {
            $this->cleanupFaceData($user);
        }

        $user->delete();
        return back()->with('success', 'User account ' . $user->email . ' deleted.');
    }

    /**
     * Delete any attendance session.
     */
    public function deleteSession(AttendanceSession $session)
    {
        $session->delete();
        return back()->with('success', 'Attendance session deleted.');
    }

    /**
     * "Login As" feature to impersonate another user for debugging.
     */
    public function loginAs(User $user)
    {
        session(['admin_impersonating_id' => Auth::id()]);
        Auth::login($user);

        if($user->isLecturer()) return redirect()->route('lecturer.dashboard');
        if($user->isStudent()) return redirect()->route('student.dashboard');

        return redirect('/')->with('success', 'Impersonating ' . $user->name);
    }


    /**
     * Manually deletes a student's face enrollment to allow re-enrollment.
     * --- MODIFIED ---
     */
    public function deleteEnrollment(User $user)
    {
        if (!$user->isStudent() || !$user->face_template_path) {
            return back()->with('error', 'This user does not have a face enrollment to delete.');
        }

        try {
            // Call the cleanup helper
            $this->cleanupFaceData($user);

            // --- MODIFIED BLOCK ---
            // Update the user's record in the database
            $user->face_template_path = null;
            $user->requesting_face_change = false; // <-- Reset the request flag
            $user->save();
            // --- END MODIFIED BLOCK ---

            $message = $user->requesting_face_change
                ? 'Request for ' . $user->name . ' accepted. They can now re-enroll.'
                : 'Successfully deleted enrollment for ' . $user->name . '. They can now enroll again.';

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Private function to delete face files from the python service.
     */
    private function cleanupFaceData(User $user)
    {
        $faceDataDir = realpath(base_path('../python_microservice/face_data'));

        if(!$faceDataDir || !File::isDirectory($faceDataDir)) {
            Log::error("Face data directory not found at: " . base_path('../python_microservice/face_data'));
            return;
        }

        $studentFaceDir = $faceDataDir . '/student_' . $user->id;
        if (File::isDirectory($studentFaceDir)) {
            File::deleteDirectory($studentFaceDir);
        }

        $pickleFile = $faceDataDir . '/representations_vgg_face.pkl';
        if(File::exists($pickleFile)) {
            File::delete($pickleFile);
        }
    }
}
