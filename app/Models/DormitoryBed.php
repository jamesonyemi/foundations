<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DormitoryBed extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = array('id');

    public function dormitory_room()
    {
        return $this->belongsTo(DormitoryRoom::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function setStudentIdAttribute($student_id)
    {
        if ($student_id) {
            $this->attributes['student_id'] = $student_id;
        } else {
            $this->attributes['student_id'] = NULL;
        }
    }
}
