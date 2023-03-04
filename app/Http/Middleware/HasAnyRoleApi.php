<?php

namespace App\Http\Middleware;

use Closure;
use Dingo\Api\Routing\Helpers;
use JWTAuth;
use Sentinel;

class HasAnyRoleApi
{
    use Helpers;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        foreach ($roles as $role) {
            // if user has given role, continue processing the request
            $user = JWTAuth::parseToken()->authenticate();
            if (! $user->inRole($role)) {
                return response()->json(['error' => 'could_not_access'], 500);
            }

            return $next($request);
        }
        // user didn't have any of required roles, return Forbidden error
        return response()->json(['error' => 'could_not_access'], 500);
    }
}
