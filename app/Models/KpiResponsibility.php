<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiResponsibility extends Model
{
    //
    /*use SoftDeletes;*/

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];





    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function responsibilities()
    {
        return $this->hasMany(KpiResponsibility::class, 'kpi_id', 'kpi_id');
    }

    public function employee_kpi_timelines()
    {
        return $this->hasMany(EmployeeKpiTimeline::class, 'kpi_id', 'kpi_id');
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id', 'id');
    }


    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_employee_id', 'id');
    }

    public function signOff()
    {
        return $this->hasMany(EmployeeKpiSignOff::class, 'supervisor_employee_id', 'id');
    }


    public function getKpiTitleAttribute()
    {
        return "{$this->kpi->title}";
    }



}
