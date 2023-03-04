<?php

namespace App\Models;

use App\Helpers\Settings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceImprovement extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setEndDateAttribute($date)
    {
        if ($date!=null && $date!="") {
            $this->attributes['end_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
        }
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


}
