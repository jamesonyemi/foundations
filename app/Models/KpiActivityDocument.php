<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiActivityDocument extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function employee_kpi_activity()
    {
        return $this->belongsTo(EmployeeKpiActivity::class);
    }

}
