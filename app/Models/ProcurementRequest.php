<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRequest extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at', 'approved_date'];
    protected $guarded = ['id'];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }


    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }

    public function assigned_employee()
    {
        return $this->belongsTo(Employee::class, 'employee_asigned_to_id');
    }


}
