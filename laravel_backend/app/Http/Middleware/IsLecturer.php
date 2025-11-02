<?php
// path: laravel_backend/app/Http/Middleware/IsLecturer.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsLecturer
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated AND has the role of 'lecturer'
        if (Auth::check() && Auth::user()->isLecturer()) {
            return $next($request); // User is a lecturer, proceed
        }

        // If not, redirect them to the home page
        return redirect('/')->with('error', 'You do not have access to this page.');
    }
}