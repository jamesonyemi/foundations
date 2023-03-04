<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSupervisor extends Model
{
    //


    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_supervisor_id', 'id');
    }



}
