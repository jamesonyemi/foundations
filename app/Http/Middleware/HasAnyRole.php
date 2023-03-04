<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class HasAnyRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        // Return Not Authorized error, if user has not logged in
        if (Sentinel::guest()) {
            return redirect()->guest('/');
        }

        foreach ($roles as $role) {
            // if user has given role, continue processing the request
            if (Sentinel::inRole($role)) {
                return $next($request);
            }
        }
        // user didn't have any of required roles, return Forbidden error
        return redirect()->guest('/');
    }
}
