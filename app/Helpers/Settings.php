<?php

/**
 * Created by PhpStorm.
 * User: Tj
 * Date: 6/29/2016
 * Time: 3:11 PM
 */

namespace App\Helpers;

use App\Models\Setting;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Settings
{
    public static function get($field)
    {
        return @Setting::whereSettingKey($field)->first()->setting_value;
    }

    public static function set($key, $value)
    {
        Setting::whereSettingKey($key)->update(['setting_value' => $value]);
    }
}
