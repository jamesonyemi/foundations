<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class Applicant
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
        if (! Sentinel::inRole('applicant')) {
            return redirect()->guest('/');
        }

        return $next($request);
    }
}
