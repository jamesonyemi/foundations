<?php

namespace App\Helpers;

use App\Helpers\Settings;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

class NatureDevValidator
{
    public static function is_connected()
    {
        $connected = @fsockopen('www.google.com', 80);
        if ($connected) {
            $is_conn = true;
            fclose($connected);
        } else {
            $is_conn = false;
        }

        return $is_conn;
    }

    public static function installPurchase(Request $request)
    {
        $license = ($request->get('license') != '') ? $request->get('license') : config('code.license');
        $secret = ($request->get('secret') != '') ? $request->get('secret') : config('code.secret');
        $email = ($request->get('email') != '') ? $request->get('email') : config('code.email');
        $url = getenv('APP_VERIFIER').'ninstaller';

        $postData = [
            'version' => config('app.version'),
            'secret' => $secret,
            'license' => $license,
            'email' => $email,
            'product_code' => config('app.product_code'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData, false);
    }

    public static function update(Request $request)
    {
        $url = getenv('APP_VERIFIER').'nupdate';
        $postData = [
            'secret' => Settings::get('secret'),
            'license' => Settings::get('license'),
            'email' => Settings::get('email'),
            'product_code' => config('app.product_code'),
            'version' => config('app.version'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData, false);
    }

    public static function verifyPurchase(Request $request)
    {
        $license = ($request->get('license') != '') ? $request->get('license') : config('code.license');
        $secret = ($request->get('secret') != '') ? $request->get('secret') : config('code.secret');
        $email = ($request->get('email') != '') ? $request->get('email') : config('code.email');
        $url = getenv('APP_VERIFIER').'nverifier';

        $postData = [
            'version' => config('app.version'),
            'secret' => $secret,
            'license' => $license,
            'email' => $email,
            'product_code' => config('app.product_code'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData);
    }

    public static function complete(Request $request)
    {
        $url = getenv('APP_VERIFIER').'nactivate';
        $postData = [
            'secret' => Settings::get('secret'),
            'license' => Settings::get('license'),
            'email' => Settings::get('email'),
            'product_code' => config('app.product_code'),
            'version' => config('app.version'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData, false);
    }

    public static function releaseLicense(Request $request)
    {
        $url = getenv('APP_VERIFIER').'nlicense-release';

        $postData = [
            'secret' => Settings::get('secret'),
            'license' => Settings::get('license'),
            'email' => Settings::get('email'),
            'product_code' => config('app.product_code'),
            'version' => config('app.version'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData);
    }

    public static function write2Config($data)
    {
        foreach ($data as $key => $value) {
            Settings::set($key, $value);
        }
        Settings::set('envato', 'no');
    }

    public static function postCurl($url, $postData, $writeConfig = true)
    {
        $response = Curl::to($url)
            ->withData($postData)
            ->post();
        $response = json_decode($response, true);
        $config = [];
        if (isset($response['status']) && $response['status'] == 'success') {
            $config['license'] = $postData['license'];
            $config['secret'] = $postData['secret'];
            $config['email'] = $postData['email'];
        } else {
            $config['license'] = '';
            $config['secret'] = '';
            $config['email'] = '';
        }
        if ($writeConfig) {
            self::write2Config($config);
        }

        return $response;
    }
}
