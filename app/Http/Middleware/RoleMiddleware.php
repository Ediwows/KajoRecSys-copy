<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            return match (auth()->user()->role) {
                'guidance_counselor' => redirect()->route('guidance.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                'teacher' => redirect()->route('teacher.dashboard'),
                default => redirect()->route('home'),
            };
        }

        return $next($request);
    }
}