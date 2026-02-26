<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware(['auth','role:superadmin|admin'])
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Unauthorized'
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Unauthorized action for your role. Required role:' . implode(' or ', $roles)
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        if ($user->isActive() === false) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Your account is inactive. Please contact administrator.'
                ], 403);
            }
            abort(403, 'Your account is inactive.');
        }

        return $next($request);
    }
}
