<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyYear extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('year', function (Builder $builder) {
        @$school= Company::find(session('current_company'));;

        if (isset($school))
        {
            if ($school->stand_alone == 1) {
                $builder->where('company_id', session('current_company'));;
            }
            else {
                $builder->where('group_id', $school->sector->group_id);

            }

        }

        });


    }


    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function admittedStudents()
    {
        return $this->hasMany(Employee::class, 'company_year_id')
            ->where('students.section_id', '>', 0)
            ->where('students.direction_id', '>', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->whereNull('students.deleted_at')
            ->whereNull('users.deleted_at');
    }


    public function RegisteredStudents()
    {
        return $this->hasMany(Employee::class, 'company_year_id')
            ->whereHas('registration')
            ->where('students.section_id', '>', 0)
            ->where('students.direction_id', '>', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->whereNull('students.deleted_at')
            ->whereNull('users.deleted_at');
    }

    public function deferredStudents()
    {
        return $this->hasMany(StudentDeferral::class, 'company_year_id');
    }

    public function dropStudents()
    {
        return $this->hasMany(StudentDrop::class, 'company_year_id');
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'company_year_id')->where('applicants.section_id', '>', 0);
    }

    public function activeStudents()
    {
        return $this->belongsToMany(Employee::class, 'student_statuses')
            ->where('students.company_year_id', '=', $this->id)
            ->orderBy('students.id')
            ->distinct('students.id');
    }

    public function adminIntakeDates()
    {
        return $this->hasMany(AdmIntakeDate::class);
    }

    public function getBscOpenAttribute_()
    {
        return true;

    }

///*
//    public function getBscOpenAttribute()
//    {
//        $currentDate= now()->format('Y-m-d');
//        /*->where('study_materials.date_off', '>=', date('Y-m-d'))*/
//        if ($this->bsc_close_date >= date('Y-m-d'))
//        {
//        return true;
//        }
//        else
//            return  false;
//    }*/


    public function getBscOpenAttribute()
    {
        $sector_id = Company::find(session('current_company'))->sector_id;
        $sector= Sector::find($sector_id);
        if ($sector->bsc_close_date >= date('Y-m-d'))
        {
            return true;
        }
        else
            return  false;
    }
}
