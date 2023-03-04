<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use mysql_xdevapi\Table;

class EmployeeRequestApprover extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'employee_request_approvers';

    /**
     * Each link can have many tags.
     *
     */
    public function employeeRequest()
    {
        return $this->belongsTo(EmployeeRequest::class);
    }

   public function employee()
    {
        return $this->belongsTo(Employee::class);
    }



}
