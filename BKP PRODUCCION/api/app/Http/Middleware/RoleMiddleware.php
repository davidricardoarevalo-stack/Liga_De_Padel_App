<?php
namespace App\Http\Middleware;

use Closure;

class RoleMiddleware {
    /**
     * Handle an incoming request and ensure user has the required role.
     * Usage in routes: ['middleware' => 'role:Administrador']
     */
    public function handle($request, Closure $next, $role = null)
    {
        $user = $request->get('auth_user');
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        if ($role) {
            // allow comma separated roles
            $allowed = array_map('trim', explode(',', $role));
            if (!in_array($user->role, $allowed)) {
                return response()->json(['error' => 'Forbidden - insufficient role'], 403);
            }
        }
        return $next($request);
    }
}
