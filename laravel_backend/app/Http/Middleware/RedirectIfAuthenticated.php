<?php
// path: laravel_backend/app/Http/Middleware/RedirectIfAuthenticated.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // --- MODIFIED BLOCK ---
                $user = Auth::guard($guard)->user();

                // Check for admin first
                if ($user->isAdmin()) {
                    return redirect('/admin/dashboard');
                }
                if ($user->isLecturer()) {
                    return redirect('/lecturer/dashboard');
                }
                if ($user->isStudent()) {
                    return redirect('/student/dashboard');
                }
                // --- END MODIFIED BLOCK ---

                // Fallback (though shouldn't be reached)
                return redirect('/home');
            }
        }

        return $next($request);
    }
}