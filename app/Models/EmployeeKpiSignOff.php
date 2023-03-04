<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeKpiSignOff extends Model
{

    protected $table = 'employee_kpi_sign_offs';
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'employee_id',
        'company_year_id',
        'status',
        'supervisor_sign_off_date',
        'self_sign_off_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function company_year()
    {
        return $this->belongsTo(CompanyYear::class);
    }

}
