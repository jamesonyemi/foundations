<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DormitoryRoom extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = array('id');

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }


    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'registrations', 'dormitory_room_id', 'student_id');
    }


    public function allocation()
    {
        return $this->hasMany(Registration::class, 'dormitory_room_id')
            ->where('registrations.company_year_id', session('current_company_year'));
    }

    public function allocationCheck()
    {
        return $this->hasMany(Registration::class, 'dormitory_room_id')
            ->where('registrations.company_year_id', session('current_company_year'));
    }

    public function free()
    {
        return $this->hasMany(Registration::class, 'dormitory_room_id')
            ->where('registrations.company_year_id', session('current_company_year'));

    }

    public function room()
    {
        return $this->hasMany(Registration::class, 'dormitory_room_id')
            ->join('employees', 'employees.id', '=', 'registrations.student_id')
            ->where('registrations.dormitory_room_id', $this->id)
            ->join( 'student_statuses', 'student_statuses.student_id', '=', 'employees.id' )
            ->where('student_statuses.attended','=','1')
            ->where('employees.company_id', session('current_company'));
    }





}
