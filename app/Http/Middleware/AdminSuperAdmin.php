<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class AdminSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! Sentinel::inRole('admin_super_admin')) {
            return redirect()->guest('/');
        }

        return $next($request);
    }
}
