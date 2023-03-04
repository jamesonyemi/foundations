<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class KitchenStaff
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
        if (! Sentinel::inRole('kitchen_Staff')) {
            return redirect()->guest('/');
        }

        return $next($request);
    }
}
