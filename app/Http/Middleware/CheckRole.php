<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin has full bypass access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized access: You do not have permission to view this section.');
    }
}
