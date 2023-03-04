<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procurement extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function assigned_employee()
    {
        return $this->belongsTo(Employee::class, 'employee_asigned_to_id');
    }


    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class, 'procurement_category_id');
    }
}
