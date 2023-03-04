<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mark extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function setExamIdAttribute($exam_id)
    {
        if ($exam_id) {
            $this->attributes['exam_id'] = $exam_id;
        } else {
            $this->attributes['exam_id'] = null;
        }
    }

    public function student()
    {
        return $this->belongsTo(Employee::class, 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function mark_type()
    {
        return $this->belongsTo(MarkType::class, 'mark_type_id');
    }

    public function mark_value()
    {
        return $this->belongsTo(MarkValue::class, 'mark_value_id');
    }

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
}
