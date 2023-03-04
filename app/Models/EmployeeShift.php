<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeShift extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->hasMany(Employee::class);
    }

    public function shiftOffDays()
    {
        return $this->hasMany(EmployeeShiftOffDay::class);
    }

    public function school()
    {
        return $this->belongsTo(Company::class);
    }

}
