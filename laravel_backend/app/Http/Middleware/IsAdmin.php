<?php
// path: laravel_backend/app/Http/Middleware/IsAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated AND has the role of 'admin'
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request); // User is an admin, proceed
        }

        // If not, redirect them to the home page
        return redirect('/')->with('error', 'You do not have administrative access.');
    }
}
