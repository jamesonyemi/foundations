<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class AdmissionWaecExamSubject extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function school()
    {
        return $this->belongsTo(School::class, 'company_id');
    }

    public function admissionWaecExam()
    {
        return $this->belongsTo(AdmissionWaecExam::class, 'admission_waec_exam_id');
    }

    public function WaecSubject()
    {
        return $this->belongsTo(WaecSubject::class, 'waec_subject_id');
    }

    public function WaecSubjectGrade()
    {
        return $this->belongsTo(WaecSubjectGrade::class, 'waec_subject_grade_id');
    }


}
