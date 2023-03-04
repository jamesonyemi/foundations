<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Direction extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function total()
    {
        return $this->hasMany(Student::class, 'direction_id');
    }

    public function admission()
    {
        return $this->hasMany(Student::class, 'direction_id')->where('status', '=', 'active')
            ->whereHas('admission', function ($query) {
                /*$query->whereStatus(1);*/
            })
            ->where('students.company_year_id', '=', session('current_company_year'))
            ->where('students.semester_id', '=', session('current_company_semester'));
    }


    public function admission_registrations()
    {
        return $this->hasMany(Student::class, 'direction_id')->where('status', '=', 'active')->whereHas('registration', function ($q) {
            $q->where('registrations.company_year_id', session('current_company_year'))
                ->where('students.company_id', session('current_company'))
                ->where('students.semester_id', '=', session('current_company_semester'))
                ->where('registrations.semester_id', session('current_company_semester'));
        });
    }


    public function section()
    {
        return $this->belongsTo(Department::class);
    }

    public function getAllActive()
    {
        return $this->hasManyThrough(StudentStatus::class, Student::class, 'direction_id', 'student_id')
            ->where('students.company_id', session('current_company'))
            ->where('student_statuses.semester_id', session('current_company_semester'));
    }



    public function registrations()
    {
        return $this->hasMany(Student::class, 'direction_id')->where('status', '=', 'active')->whereHas('registration', function ($q) {
            $q->where('registrations.company_year_id', session('current_company_year'))
                ->where('students.company_id', session('current_company'))
                ->where('registrations.semester_id', session('current_company_semester'));
        });
    }
}
