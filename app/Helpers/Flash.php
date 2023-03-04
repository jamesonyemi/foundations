<?php

/**
 * Created by PhpStorm.
 * User: Tj
 * Date: 6/29/2016
 * Time: 3:11 PM
 */

namespace App\Helpers;

use App\Models\Setting;
use Brian2694\Toastr\Facades\Toastr;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Session;

class Flash
{
    public static function error($message)
    {
        Session()->flash('error', $message);
        Toastr::error($message.':)', 'Notification');

    }

    public static function success($message)
    {
        Session()->flash('success', $message);
        Toastr::success($message.':)', 'Notification');
    }

    public static function warning($message)
    {
        Session()->flash('warning', $message);
        Toastr::warning($message.':)', 'Notification');
    }

    public static function info($message)
    {
        Session()->flash('info', $message);
        Toastr::info($message.':)', 'Notification');
    }
}
