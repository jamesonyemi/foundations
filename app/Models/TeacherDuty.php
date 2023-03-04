<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherDuty extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setStartDateAttribute($date)
    {
        $this->attributes['start_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getStartDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }
    public function setEndDateAttribute($date)
    {
        $this->attributes['end_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getEndDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }
}
