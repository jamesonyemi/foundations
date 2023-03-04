<?php

namespace App\Http\Controllers;

use App\Helpers\EnvatoValidator;
use App\Helpers\NatureDevValidator;
use App\Helpers\Settings;
use App\Http\Requests\VerifyRequest;

class VerifyController extends Controller
{
    public function index()
    {
        return view('verify.index');
    }

    public function verify(VerifyRequest $request)
    {
        if (Settings::get('envato') == 'no') {
            if (NatureDevValidator::is_connected()) {
                $verify = NatureDevValidator::verifyPurchase($request);
                if ($verify['status'] == 'success') {
                    return redirect()->to('/');
                }
                unlink(storage_path('installed'));

                return redirect()->back()->withErrors(['message' => $verify['message']]);
            }
        } else {
            if (EnvatoValidator::is_connected()) {
                $verify = EnvatoValidator::verifyPurchase($request);
                if ($verify['status'] == 'success') {
                    return redirect()->to('/');
                }
                unlink(storage_path('installed'));

                return redirect()->back()->withErrors(['message' => $verify['message']]);
            }
        }
        unlink(storage_path('installed'));

        return redirect()->back()->withErrors(['message' => trans('verify.no_internet')]);
    }
}
