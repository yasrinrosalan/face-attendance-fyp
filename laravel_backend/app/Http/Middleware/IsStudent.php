<?php
// path: laravel_backend/app/Http/Middleware/IsStudent.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated AND has the role of 'student'
        if (Auth::check() && Auth::user()->isStudent()) {
            return $next($request); // User is a student, proceed
        }

        // If not, redirect them to the home page
        return redirect('/')->with('error', 'You do not have access to this page.');
    }
}
