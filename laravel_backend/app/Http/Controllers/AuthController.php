<?php
// path: laravel_backend/app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password; // Added for password reset
use Illuminate\Auth\Events\PasswordReset; // Added for password reset
use Illuminate\Support\Str;              // Added for password reset
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

            // Note: Admin check removed based on updated system scope
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

        return redirect('/');
    }

    // ==========================================
    // --- Forgot / Reset Password Methods ---
    // ==========================================

    // 1. Show the form to request a reset link
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // 2. Process the email and send the link
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['success' => 'A password reset link has been sent to your email!'])
                    : back()->withErrors(['email' => 'Unable to send reset link. Try again.']);
    }

    // 3. Show the actual reset password form (when they click the email link)
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    // 4. Save the new password to the database
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', 'Your password has been successfully reset! Please login.')
                    : back()->withErrors(['email' => [__($status)]]);
    }
}