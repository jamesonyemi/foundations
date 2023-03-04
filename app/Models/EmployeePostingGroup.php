<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class EmployeePostingGroup extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_posting_group_id');
    }



    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

}
