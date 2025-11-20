<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $roles = func_get_args();
        array_shift($roles);
        array_shift($roles);

        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        if($request->is('admin') || $request->is('admin/*') || $request->routeIs('admin.*')) {
            return response()->view('errors.403-admin', [], 403);
        }

        abort(403);
    }
}
