<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeJospongRelativeEmployment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'employee_jospong_relative_employments';
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


}
