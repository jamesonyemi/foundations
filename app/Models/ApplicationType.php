<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationType extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'application_type_id');
    }



    public function exams()
    {
        return $this->hasMany(WaecExam::class, 'application_type_id');
    }

    public function subjects()
    {
        return $this->hasMany(WaecSubject::class, 'application_type_id');
    }



    public function subjectGrades()
    {
        return $this->hasMany(WaecSubjectGrade::class, 'application_type_id');
    }

}
