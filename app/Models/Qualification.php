<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Qualification extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function applicantSchools()
    {
        return $this->hasMany(Applicant_school::class, 'qualification_id');
    }

    public function employees()
    {
        return $this->hasMany(EmployeeQualification::class);
    }

    public function qualification_framework()
    {
        return $this->hasMany(QualificationFramework::class);
    }
}
