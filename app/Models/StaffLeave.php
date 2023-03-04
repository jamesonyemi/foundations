<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffLeave extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];


    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function reliever_employee()
    {
        return $this->belongsTo(Employee::class, 'reliever_employee_id');
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function staff_leave_type()
    {
        return $this->belongsTo(StaffLeaveType::class);
    }

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setStartDateAttribute($date)
    {
        $this->attributes['start_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function setEndDateAttribute($date)
    {
        $this->attributes['end_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getStartDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }

    public function getEndDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }

    public function comments()
    {
        return $this->hasMany(StaffLeaveComment::class)->orderByDesc('id');
    }

    public function staffLeaveDocuments()
    {
        return $this->hasMany(StaffLeaveDocument::class)->orderByDesc('id');
    }

}
