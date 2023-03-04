<?php

namespace App\Http\Middleware;

use App\Models\SchoolDesktopToken;
use Closure;

class DesktopAdmin
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
        $school_desktop_token = SchoolDesktopToken::where('token', $request->token)->first();
        if (! isset($school_desktop_token->company_id)) {
            return response()->json(['error' => 'could_not_access'], 500);
        }

        return $next($request);
    }
}
