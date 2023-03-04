<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeIdeaComment extends Model
{
    //
    /*use SoftDeletes;*/

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function employeeIdea()
    {
        return $this->belongsTo(EmployeeIdea::class);
    }




   public function employee()
    {
        return $this->belongsTo(Employee::class);
    }



}
