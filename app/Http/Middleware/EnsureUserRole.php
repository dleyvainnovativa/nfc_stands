<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard by role. Registered as the "role" alias.
 *
 *   Route::middleware('role:super_admin')->group(...);
 *   Route::middleware('role:super_admin,client_admin')->group(...);
 */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Account unavailable.');
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
