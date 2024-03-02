<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateAspect
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) { // Check if the user is authenticated
            return $next($request);
        }

        return response('Unauthorized', 401);
    }
}
