<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeKpiScore extends Model
{
    //


    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'employee_kpi_scores';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'id');
    }




}
