<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiActivityStatus extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'kpi_activity_statuses';


    public function kpiActivities()
    {
        return $this->hasMany(EmployeeKpiActivity::class);
    }



}
