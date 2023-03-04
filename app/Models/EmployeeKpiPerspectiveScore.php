<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeKpiPerspectiveScore extends Model
{
    //


    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'id');
    }

    public function kpiTimeLine()
    {
        return $this->belongsTo(KpiTimeline::class);
    }


    public function perspective()
    {
        return $this->belongsTo(BscPerspective::class);
    }



}
