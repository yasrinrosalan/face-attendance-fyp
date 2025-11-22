<?php
// path: laravel_backend/app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Import the Auth facade

class HomeController extends Controller
{
    /**
     * Handle the root route (/).
     *
     * This will check if the user is logged in and redirect them
     * to their correct dashboard. If they are a guest,
     * it will show the login page.
     */
    public function index()
    {
        // Check if the user is a guest (not logged in)
        if (!Auth::check()) {
            return view('auth.login'); // Show the login page
        }

        // User is logged in, check their role
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isLecturer()) {
            return redirect()->route('lecturer.dashboard');
        }

        if ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        // Failsafe: if user has no role, just show the login page
        return view('auth.login');
    }
}