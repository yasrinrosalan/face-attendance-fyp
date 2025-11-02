<?php
// path: laravel_backend/app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // --- Registration ---
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student', // All registrations are for students
        ]);

        // Log the user in
        Auth::login($user);

        // Redirect to the student dashboard
        return redirect('/student/dashboard')->with('success', 'Registration successful!');
    }

    // --- Login ---
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validation
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Try to log in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // --- THIS IS THE CORRECTED LOGIC ---
            // Check for Admin first
            if ($user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            }
            // Check for Lecturer
            if ($user->isLecturer()) {
                return redirect()->intended('/lecturer/dashboard');
            }
            // Default to Student
            if ($user->isStudent()) {
                return redirect()->intended('/student/dashboard');
            }
            // --- END CORRECTION ---

            // Fallback just in case
            return redirect('/');
        }

        // If login fails
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // --- Logout ---
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'You have been logged out.');
    }

    /**
     * Allow an impersonating admin to return to their account.
     */
    public function returnToAdmin()
    {
        // Check if admin_impersonating_id exists in session
        if (session()->has('admin_impersonating_id')) {
            $admin_id = session('admin_impersonating_id');

            // Remove it from session
            session()->forget('admin_impersonating_id');

            // Log the original admin back in
            Auth::login(User::find($admin_id));

            // Redirect to admin dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, Admin!');
        }

        // If not impersonating, just redirect
        return redirect('/');
    }
}
