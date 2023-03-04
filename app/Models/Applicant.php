<?php

namespace App\Models;

use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Applicant extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schools()
    {
        return $this->hasMany(Applicant_school::class, 'applicant_id');
    }

    public function documents()
    {
        return $this->hasMany(Applicant_doc::class, 'applicant_id');
    }

    public function works()
    {
        return $this->hasMany(Applicant_work::class, 'applicant_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }


    public function school()
    {
        return $this->belongsTo(School::class, 'company_id');
    }


    public function programme1()
    {
        return $this->belongsTo(Direction::class, 'first_choice_prog_id');
    }

    public function programme2()
    {
        return $this->belongsTo(Direction::class, 'second_choice_prog_id');
    }

    public function programme3()
    {
        return $this->belongsTo(Direction::class, 'third_choice_prog_id');
    }

    public function session()
    {
        return $this->belongsTo(\App\Models\Session::class, 'session_id');
    }

    public function academicyear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function intakeperiod()
    {
        return $this->belongsTo(IntakePeriod::class, 'intake_period_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }

    public function applicationType()
    {
        return $this->belongsTo(ApplicationType::class, 'application_type_id', 'id');
    }

    public function admissionlevel()
    {

        return $this->belongsTo(Level::class, 'level_of_adm', 'id');
    }

    public function entrymode()
    {
        return $this->belongsTo(EntryMode::class, 'entry_mode_id', 'id');
    }

    public function maritalstatus()
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function applicantNotes()
    {
        return $this->hasMany(ApplicantNote::class, 'applicant_id')->orderBy('id', 'DESC');;
    }



    public function waecExams()
    {
        return $this->hasMany(AdmissionWaecExam::class, 'applicant_id');
    }

    public function subjects()
    {
        return $this->hasMany(AdmissionWaecExamSubject::class, 'applicant_id');
    }

    public function nonWaecExams()
    {
        return $this->hasMany(AdmissionNonWaecExam::class, 'applicant_id');
    }
}
