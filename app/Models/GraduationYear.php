<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraduationYear extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function school()
    {
        return $this->belongsTo(School::class, 'company_id');
    }

    public function admittedStudents()
    {
        return $this->hasMany(Student::class, 'graduation_year_id');
    }

    public function deferredStudents()
    {
        return $this->hasMany(StudentDeferral::class, 'graduation_year_id');
    }

    public function dropStudents()
    {
        return $this->hasMany(StudentDrop::class, 'graduation_year_id');
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'graduation_year_id');
    }

    public function activeStudents()
    {
        return $this->belongsToMany(Student::class, 'student_statuses')
            ->where('students.graduation_year_id', '=', $this->id)
            ->orderBy('students.id')
            ->distinct('students.id');
    }

    public function adminIntakeDates()
    {
        return $this->hasMany(AdmIntakeDate::class);
    }
}
