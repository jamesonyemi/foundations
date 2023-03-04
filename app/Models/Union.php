<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Union extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function total()
    {
        return $this->hasMany(Employee::class, 'union_id');
    }

    public function section()
    {
        return $this->belongsTo(Department::class);
    }



    public function registrations()
    {
        return $this->hasMany(Employee::class, 'direction_id')->whereHas('registration', function ($q) {
            $q->where('registrations.company_year_id', session('current_company_year'))
                ->where('students.company_id', session('current_company'))
                ->where('registrations.semester_id', session('current_company_semester'));
        });
    }
}
