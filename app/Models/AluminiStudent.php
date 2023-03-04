<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AluminiStudent extends Model
{
    use SoftDeletes;

    protected $dates = [ 'deleted_at' ];

    protected $guarded = array ( 'id' );

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function school_year()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function alumini()
    {
        return $this->belongsTo(Alumini::class);
    }
}
