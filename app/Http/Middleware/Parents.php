<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class Parents
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
        if (! Sentinel::inRole('parent')) {
            return redirect()->guest('/');
        }

        return $next($request);
    }
}
