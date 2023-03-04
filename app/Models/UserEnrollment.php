<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class UserEnrollment extends Model
{
    protected $table = 'user_enrolments';


    public function enrol()
    {
        return $this->hasOne(Enrol::class, 'id', 'enrolid');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id');
    }
}
