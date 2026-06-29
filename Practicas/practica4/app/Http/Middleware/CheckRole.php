<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            abort(403);
        }

        foreach ($roles as $role) {
            if (Auth::user()->role === $role) {
                return $next($request);
            }
        }

        abort(403);
    }
}
