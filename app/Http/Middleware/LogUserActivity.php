<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) > 15) {
                $user->last_login_at = now();
                $user->last_login_ip = $request->ip();
                $user->saveQuietly();
            }
        }

        return $next($request);
    }
}
