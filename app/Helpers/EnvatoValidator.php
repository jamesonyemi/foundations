<?php

namespace App\Helpers;

use App\Helpers\Settings;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

class EnvatoValidator
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
        $purchase_code = ($request->get('purchase_code') != '') ? $request->get('purchase_code') : config('code.purchase_code');
        $envato_username = ($request->get('envato_username') != '') ? $request->get('envato_username') : config('code.envato_username');
        $envato_email = ($request->get('envato_email') != '') ? $request->get('envato_email') : config('code.envato_email');
        $url = getenv('APP_VERIFIER').'installer';

        $postData = [
            'version' => config('app.version'),
            'envato_username' => $envato_username,
            'purchase_code' => $purchase_code,
            'envato_email' => $envato_email,
            'product_code' => config('app.product_code'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData, false);
    }

    public static function update(Request $request)
    {
        $url = getenv('APP_VERIFIER').'update';
        $postData = [
            'envato_username' => Settings::get('envato_username'),
            'purchase_code' => Settings::get('purchase_code'),
            'envato_email' => Settings::get('envato_email'),
            'product_code' => config('app.product_code'),
            'version' => config('app.version'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData, false);
    }

    public static function verifyPurchase(Request $request)
    {
        $purchase_code = ($request->get('purchase_code') != '') ? $request->get('purchase_code') : config('code.purchase_code');
        $envato_username = ($request->get('envato_username') != '') ? $request->get('envato_username') : config('code.envato_username');
        $envato_email = ($request->get('envato_email') != '') ? $request->get('envato_email') : config('code.envato_email');
        $url = getenv('APP_VERIFIER').'verifier';

        $postData = [
            'version' => config('app.version'),
            'envato_username' => $envato_username,
            'purchase_code' => $purchase_code,
            'envato_email' => $envato_email,
            'product_code' => config('app.product_code'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData);
    }

    public static function complete(Request $request)
    {
        $url = getenv('APP_VERIFIER').'activate';
        $postData = [
            'envato_username' => Settings::get('envato_username'),
            'purchase_code' => Settings::get('purchase_code'),
            'envato_email' => Settings::get('envato_email'),
            'product_code' => config('app.product_code'),
            'version' => config('app.version'),
            'install_url' => $request->url(),
        ];

        return self::postCurl($url, $postData, false);
    }

    public static function releaseLicense(Request $request)
    {
        $url = getenv('APP_VERIFIER').'license-release';

        $postData = [
            'envato_username' => Settings::get('envato_username'),
            'purchase_code' => Settings::get('purchase_code'),
            'envato_email' => Settings::get('envato_email'),
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
        Settings::set('envato', 'yes');
    }

    public static function postCurl($url, $postData, $writeConfig = true)
    {
        $response = Curl::to($url)
            ->withData($postData)
            ->post();
        $response = json_decode($response, true);
        $config = [];
        if (isset($response['status']) && $response['status'] == 'success') {
            $config['purchase_code'] = $postData['purchase_code'];
            $config['envato_username'] = $postData['envato_username'];
            $config['envato_email'] = $postData['envato_email'];
        } else {
            $config['purchase_code'] = '';
            $config['envato_username'] = '';
            $config['envato_email'] = '';
        }
        if ($writeConfig) {
            self::write2Config($config);
        }

        return $response;
    }
}
