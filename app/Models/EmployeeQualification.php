<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class EmployeeQualification extends Model
{

    protected $guarded = ['id'];


    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
