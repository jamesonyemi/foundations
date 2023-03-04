<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class Supplier
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
        if (! Sentinel::inRole('supplier')) {
            return redirect()->guest('/');
        }

        return $next($request);
    }
}
