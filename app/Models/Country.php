<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'countries';

    public function students()
    {
        return $this->hasMany(Student::class, 'country_id');
    }

    public function admission()
    {
        return $this->hasMany(Student::class, 'country_id')->where('status', '=', 'active')
            ->whereHas('admission', function ($query) {
                /*$query->whereStatus(1);*/
            })
            ->where('students.company_year_id', '=', session('current_company_year'))
            ->where('students.semester_id', '=', session('current_company_semester'));
    }

    public function getAllActive()
    {
        return $this->hasManyThrough(StudentStatus::class, Student::class, 'country_id', 'student_id')
            ->where('students.company_id', session('current_company'));;
    }

    public function getAllAlumni()
    {
        return $this->hasManyThrough(Alumnus::class, Student::class, 'country_id', 'student_id')
            ->where('students.company_id', session('current_company'));;
    }

    public function getAllDeferral()
    {
        return $this->hasManyThrough(StudentDeferral::class, Student::class, 'country_id', 'student_id')
            ->where('student_deferrals.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('student_deferrals.semester_id', session('current_company_semester'));
    }

    public function getAllDrop()
    {
        return $this->hasManyThrough(StudentDrop::class, Student::class, 'country_id', 'student_id')
            ->where('student_drops.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('student_drops.semester_id', session('current_company_semester'));
    }

    public function getAllGraduating()
    {
        return $this->hasManyThrough(StudentGraduation::class, Student::class, 'country_id', 'student_id')
            ->where('student_graduations.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('student_graduations.semester_id', session('current_company_semester'));
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'country_id')
            ->where('applicants.company_year_id', '=', session('current_company_year'));
    }
}
