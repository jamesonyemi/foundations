<?php

namespace App\Http\Middleware;

use App\Helpers\NatureDev;
use App\Helpers\Settings;
use Closure;

class VerifyInstallation
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
        if (! file_exists(storage_path('installed')) && ! $request->is('install*')) {
            return redirect()->to('install');
        }

        if (file_exists(storage_path('installed')) && $request->is('install*')
            && ! $request->is('install/settings') && ! $request->is('install/email_settings')
            && ! $request->is('install/complete')
        ) {
            return redirect()->to('/');
        }
        if ((NatureDev::checkDBConnection() && ! strlen(Settings::get('purchase_code')) == 36 &&
            ! strlen(Settings::get('envato_username')) >= 3 && ! $request->is('install*')
            && ! $request->is('verify')) ||
           (NatureDev::checkDBConnection() && ! strlen(Settings::get('license')) == 28 &&
            ! strlen(Settings::get('secret')) >= 3 && ! $request->is('install*')
            && ! $request->is('verify'))) {
            return redirect()->to('verify');
        }

        return $next($request);
    }
}
