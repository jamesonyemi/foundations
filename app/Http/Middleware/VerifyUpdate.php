<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyUpdate
{
    protected $except = [
        'api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if (file_exists(storage_path('installed')) && ! $request->is('update*') && ! $request->is('verify')) {
            $version = config('app.version');
            $current_version = Schema::hasTable('version') ? (isset(DB::table('version')->first()->version) ? DB::table('version')->first()->version : 0) : 0;

            if ($current_version > 0 && abs(($version - $current_version) / $current_version) > 0.00001) {
                return redirect()->to('update/'.$current_version);
            } elseif ($current_version == 0 || is_null($current_version)) {
                return redirect()->to('update/0');
            }
        }

        return $next($request);
    }
}
