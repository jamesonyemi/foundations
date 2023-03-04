<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiActivityComment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function employee_kpi_activity()
    {
        return $this->belongsTo(EmployeeKpiActivity::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function comments()
    {
        return $this->hasMany(KpiActivityComment::class, 'parent_id');
    }

}
