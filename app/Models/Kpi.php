<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'kpis';



    public function getFullTitleAttribute()
    {
        return "{$this->kpiObjective->full_title} - {$this->title}"?? "{$this->title}";
    }

   /* public function getcascadAttribute()
    {
        if ($this->kpiResponsibilities())
        {
            return true;
        }
    }*/

    public function kpiObjective()
    {
        return $this->belongsTo(KpiObjective::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function kpiTimeline()
    {
        return $this->belongsTo(KpiTimeline::class);
    }

    public function kpiTimelines()
    {
        return $this->hasMany(EmployeeKpiTimeline::class);
    }

    public function employee_kpiTimelines()
    {
        return $this->hasMany(EmployeeKpiTimeline::class);
    }



    public function kpiPerformance()
    {
        return $this->hasMany(KpiPerformance::class);
    }

    public function competencyGaps()
    {
        return $this->hasMany(CompetencyGap::class);
    }


    public function kpiActivities()
    {
        return $this->hasMany(EmployeeKpiActivity::class)->orderBy('due_date', 'ASC');
    }

    public function kpiResponsibilities()
    {
        return $this->hasMany(KpiResponsibility::class);
    }

    public function employeeKpiTimeline()
    {
        return $this->hasMany(EmployeeKpiTimeline::class);
    }


    public function kpiPerformanceReview()
    {
        return $this->hasMany(KpiPerformanceReview::class);
    }

    public function kpiResponsibleEmployees()
    {
        return $this->hasManyThrough(Employee::class, KpiResponsibility::class, 'responsible_employee_id', 'id', 'id');
    }

    public function employeeResponsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_employee_id');
    }

    public function kpiSupervisors()
    {
        return $this->hasManyThrough(Employee::class, KpiResponsibility::class, 'supervisor_employee_id', 'id', 'id');
    }



    public function getTimeLinesAttribute($employee_id)
    {
        $timeline = '';
        if ($this->q1 == 1) {
            $timeline .= 'Q1('.@\App\Models\KpiPerformanceReview::where('employee_id',$employee_id)->where('kpi_id',$this->id)->where('kpi_timeline_id',1)->first()->agreed_rating.'), ' ;
        }
        if ($this->q2 == 1) {
            $timeline .= 'Q2('.@\App\Models\KpiPerformanceReview::where('employee_id',$employee_id)->where('kpi_id',$this->id)->where('kpi_timeline_id',2)->first()->agreed_rating.'), ';
        }
        if ($this->q3 == 1) {
            $timeline .= 'Q3('.@\App\Models\KpiPerformanceReview::where('employee_id',$employee_id)->where('kpi_id',$this->id)->where('kpi_timeline_id',3)->first()->agreed_rating.'), ';
        }
        if ($this->q4 == 1) {
            $timeline .= 'Q4('.@\App\Models\KpiPerformanceReview::where('employee_id',$employee_id)->where('kpi_id',$this->id)->where('kpi_timeline_id',4)->first()->agreed_rating.'), ';
        }
        return $timeline;
    }

    public function comments()
    {
        return $this->hasMany(KpiComment::class)->orderByDesc('id');
    }


}
