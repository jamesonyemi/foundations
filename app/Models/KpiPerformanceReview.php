<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiPerformanceReview extends Model
{
    //
    /*use SoftDeletes;*/

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function kpiTimeline()
    {
        return $this->belongsTo(KpiTimeline::class);
    }




}
