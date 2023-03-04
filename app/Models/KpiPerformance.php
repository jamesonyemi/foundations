<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiPerformance extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'kpi_performance';


    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }


    public function employeeResponsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }


}
