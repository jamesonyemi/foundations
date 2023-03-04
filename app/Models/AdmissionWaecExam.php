<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class AdmissionWaecExam extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function school()
    {
        return $this->belongsTo(School::class, 'company_id');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function exams()
    {
        return $this->belongsTo(WaecExam::class, 'waec_exam_id');
    }

    public function subjects()
    {
        return $this->hasMany(AdmissionWaecExamSubject::class, 'admission_waec_exam_id');
    }

}
