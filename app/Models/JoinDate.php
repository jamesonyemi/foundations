<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JoinDate extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setDateStartAttribute($date)
    {
        $this->attributes['date_start'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getDateStartAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }

    public function setDateEndAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            $this->attributes['date_end'] = null;
        } else {
            $this->attributes['date_end'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
        }
    }

    public function getDateEndAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }
}
