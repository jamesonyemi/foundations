<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    protected $dates = [ 'deleted_at' ];

    protected $guarded = array ( 'id' );

    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function school()
    {
        return $this->belongsTo(Company::class);
    }





    public function student()
    {
        return $this->belongsTo(Employee::class, 'student_id', 'id');
    }

    public function academicYear()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
