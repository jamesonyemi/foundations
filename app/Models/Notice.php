<?php
namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{

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

    public function notice_type()
    {
        return $this->belongsTo(NoticeType::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student_group()
    {
        return $this->belongsTo(StudentGroup::class, 'student_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Department::class, 'section_id');
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }
}
