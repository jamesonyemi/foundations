<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeIdea extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function employeeIdeationCampaign()
    {
        return $this->belongsTo(EmployeeIdeaCampaign::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    // get pending leave approval
    public function is_approver($id)
    {
        $approver = EmployeeRequestApprover::where('employee_request_id', $this->id)->whereIn('employee_id', [$id])->first();
        if ($approver)
        {
            return true;
        }

        else return false;

    }
    // get pending leave approval
    public function is_approved($id)
    {
        $approver = EmployeeRequestApprover::where('employee_request_id', $this->id)->whereIn('employee_id', [$id])->where('status', 1)->first();
        if ($approver)
        {
            return true;
        }

        else return false;

    }


    // get pending leave approval
    public function is_copied($id)
    {
        $copied = EmployeeRequestCopy::where('employee_request_id', $this->id)->whereIn('employee_id', [$id])->first();
        if ($copied)
        {
            return true;
        }

        else return false;

    }

    public function comments()
    {
        return $this->hasMany(EmployeeIdeaComment::class)->orderByDesc('id');
    }



    public function employeeIdeaDocuments()
    {
        return $this->hasMany(EmployeeIdeaDocument::class)->orderByDesc('id');
    }







}
