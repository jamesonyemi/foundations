<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dormitory extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = array('id');

    public function getPictureAttribute()
    {
        $picture = $this->attributes['picture'];

        if (empty($picture))
            return asset('uploads/avatar/avatar') . '.png';

        return asset('uploads/avatar') . '/' . $picture;
    }

    public function rooms()
    {
        return $this->hasMany(DormitoryRoom::class, 'dormitory_id');
    }

    public function allocation()
    {
        return $this->hasMany(Registration::class, 'dormitory_id')
                    ->where('registrations.school_year_id', session('current_company_year'));
    }

    public function occupation()
    {
        return $this->hasMany(Registration::class, 'dormitory_id');
    }

    public function room() {
        return $this->belongsToMany( DormitoryRoom::class,
            'registrations', 'dormitory_id',
            'dormitory_room_id' )
            ->withPivot( 'user_id')
            ->join('students', 'students.id', '=', 'registrations.student_id')
                ->where('registrations.dormitory_id', $this->id)
            ->join( 'student_statuses', 'student_statuses.student_id', '=', 'students.id' )
            ->where('student_statuses.attended','=','1')
                ->where('students.company_id', session('current_company'));
    }






}
