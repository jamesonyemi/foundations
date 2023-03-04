<?php

/**
 * Created by PhpStorm.
 * User: Tj
 * Date: 6/29/2016
 * Time: 3:11 PM
 */

namespace App\Helpers;

use App\Models\CompanySetting;
use App\Models\Setting;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanySettings
{
    public static function get($field)
    {
        return @CompanySetting::whereCompanyId(session('current_company'))->whereSettingKey($field)->first()->setting_value;
    }

    public static function set($key, $value)
    {
        @CompanySetting::whereCompanyId(session('current_company'))->whereSettingKey($key)->update(['setting_value' => $value]);
    }
}
