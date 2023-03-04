<?php

namespace App\Http\Middleware;

use App\Helpers\Settings;
use App\Models\User;
use Closure;
use Laracasts\Flash\Flash;
use Sentinel;

class Authorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission = null)
    {
        $user = $request->user();

        if ($user && (! Sentinel::inRole('admin') ||
                (Sentinel::getUser()->inRole('admin') && Settings::get('multi_school') == 'no') ||
                (Sentinel::inRole('admin') &&
                        ($user->permissions == null ||
                            ($permission == null || in_array($permission, $user->permissions)))))) {
            return $next($request);
        }
        Flash::error(trans('secure.permission_denied'));

        return redirect()->back();
    }
}
