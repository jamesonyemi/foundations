<?php
namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diary extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setDateAttribute($date)
    {
        $this->attributes['date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
