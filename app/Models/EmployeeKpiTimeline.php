<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeKpiTimeline extends Model
{
    //


    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'employee_kpi_timelines';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'id');
    }


    public function kpi_timeline()
    {
        return $this->belongsTo(KpiTimeline::class, 'kpi_timeline_id', 'id');
    }


    public function kpi_responsibility()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'kpi_id');
    }



}
