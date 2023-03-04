<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeKpiTimelineSignOff extends Model
{

    protected $table = 'employee_kpi_timeline_sign_offs';
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'employee_id',
        'company_year_id',
        'kpi_timeline_id',
        'self',
        'supervisor',
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

    public function kpi_timeline()
    {
        return $this->belongsTo(KpiTimeline::class);
    }

}
