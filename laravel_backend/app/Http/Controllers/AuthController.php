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
            'student_id' => 'required|string|max:20|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        // Log the user in
        Auth::login($user);

        return redirect('/student/dashboard')->with('success', 'Registration successful!');
    }

    // --- Login ---
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isAdmin()) return redirect()->intended('/admin/dashboard');
            if ($user->isLecturer()) return redirect()->intended('/lecturer/dashboard');
            if ($user->isStudent()) return redirect()->intended('/student/dashboard');

            return redirect('/');
        }

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

        // --- MODIFIED: Removed ->with('success', ...) ---
        return redirect('/');
    }

    // --- Return to Admin ---
    public function returnToAdmin()
    {
        if (session()->has('admin_impersonating_id')) {
            $admin_id = session('admin_impersonating_id');
            session()->forget('admin_impersonating_id');
            Auth::login(User::find($admin_id));
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, Admin!');
        }
        return redirect('/');
    }
}
