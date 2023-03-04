<?php

namespace App\Http\Middleware;

use App;
use App\LanguageSite;
use Closure;
use Config;
use Session;

class Locale
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
        $language = session('language', 'en');
        App::setLocale($language);

        return $next($request);
    }
}
