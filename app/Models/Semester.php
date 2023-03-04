<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'end'
    ];

    protected $guarded = ['id'];

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class);
    }

    public function admittedStudents()
    {
        return $this->hasMany(Employee::class, 'semester_id');
    }

    public function deferredStudents()
    {
        return $this->hasMany(StudentDeferral::class, 'semester_id');
    }

    public function dropStudents()
    {
        return $this->hasMany(StudentDrop::class, 'semester_id');
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'semester_id');
    }

    public function activeStudents()
    {
        return $this->belongsToMany(Employee::class, 'student_statuses')
            ->where('students.semester_id', '=', $this->id)
            ->orderBy('students.id')
            ->distinct('students.id');
    }

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setStartAttribute($start)
    {
        $this->attributes['start'] = Carbon::createFromFormat($this->date_format(), $start)->format('Y-m-d');
    }

    public function getStartAttribute($start)
    {
        if ($start == "0000-00-00" || $start == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($start));
        }
    }

    public function setEndAttribute($end)
    {
        $this->attributes['end'] = Carbon::createFromFormat($this->date_format(), $end)->format('Y-m-d');
    }

    public function getEndAttribute($end)
    {
        if ($end == "0000-00-00" || $end == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($end));
        }
    }

    public function students()
    {
        return $this->hasMany(Employee::class, 'semester_id');
    }

    public function registrations()
    {

        return $this->hasMany(Registration::class);
    }


    public function adminIntakeDates()
    {
        return $this->hasMany(AdmIntakeDate::class);
    }
}
