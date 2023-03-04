<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRequest extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function employeeRequestCategory()
    {
        return $this->belongsTo(EmployeeRequestCategory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    // get pending leave approval
    public function getApprovedAttribute()
    {
        $NotApproved = EmployeeRequestApprover::where('employee_request_id', $this->id)->where('status', 0)->first();
        if ($NotApproved)
        {
            return false;
        }

        else return true;

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
        return $this->hasMany(EmployeeRequestComment::class)->orderByDesc('id');
    }


    public function approvers()
    {
        return $this->hasMany(EmployeeRequestApprover::class)->orderByDesc('id');
    }


    public function copies()
    {
        return $this->hasMany(EmployeeRequestCopy::class)->orderByDesc('id');
    }

    public function employeeRequestDocuments()
    {
        return $this->hasMany(EmployeeRequestDocument::class)->orderByDesc('id');
    }







}
