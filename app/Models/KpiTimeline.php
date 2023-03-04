<?php

namespace App\Models;

use App\Helpers\GeneralHelper;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class KpiTimeline extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('group', function (Builder $builder) {
            $school = Company::find(session('current_company'));
            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->where('company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('company.sector', function ($q) use ($school) {
                        $q->where('sectors.group_id', $school->sector->group_id);;
                    });
                }
            }

        });


    }


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function year()
    {
        return $this->belongsTo(CompanyYear::class);
    }


    public function kpis()
    {
        return $this->hasMany(EmployeeKpiTimeline::class);
    }

    public function getReviewOpenAttribute()
    {

        if (($this->review_end_date >= date('Y-m-d')) && ($this->review_start_date <= date('Y-m-d')))
        {
            return true;
        }
        else
            return  false;
    }



    /*public function timeLineKpis($timeLine, $perspective_id, $employee_id)
    {
        return KpiResponsibility::whereHas('kpi.kpiObjective.kra', function ($q) use ($timeLine, $perspective_id, $employee_id) {
            $q->where('kpis.'.$timeLine, 1)->where('kras.bsc_perspective_id', $perspective_id)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->where('responsible_employee_id', $employee_id)->with('kpi', 'responsibilities')->get();
    }*/



    public function timeLineKpis($timeLine, $perspective_id, $employee_id)
    {
        return KpiResponsibility::whereHas('kpi.kpiObjective.kra', function ($q) use ($timeLine, $perspective_id, $employee_id) {
            $q->where('kras.bsc_perspective_id', $perspective_id)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->whereHas('kpi.employee_kpiTimelines', function ($q) use ($timeLine, $perspective_id, $employee_id) {
            $q->where('employee_kpi_timelines.kpi_timeline_id', $timeLine)->where('employee_kpi_timelines.employee_id', $employee_id);
        })->where('responsible_employee_id', $employee_id)->with('kpi', 'responsibilities')->get();

    }





    public function getTimelineKpiAttribute()
    {
        return Kpi::where($this->title, 1)->whereHas('employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1);
        })->get();
    }


    public function employee_timeline_kpi($employee)
    {
        return Kpi::where($this->title, 1)->whereHas('employee', function ($q) use($employee){
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.id', '=', $employee);
        })->get();
    }


    public function department_timeline_kpi($department)
    {
        return Kpi::where($this->title, 1)->whereHas('employee', function ($q) use($department){
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', '=', $department);
        })->get();
    }


    public function getTimelineActivitiesAttribute()
    {
        return EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1);
        })->get();
    }


    public function employee_timeline_activities($employee)
    {
        return EmployeeKpiActivity::whereHas('kpi.employee', function ($q) use($employee){
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.id', '=', $employee);
        })->get();
    }

    public function department_timeline_activities($department)
    {
        return EmployeeKpiActivity::whereHas('kpi.employee', function ($q) use($department){
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', '=', $department);
        })->get();
    }

    public function getTimelineCompletedActivitiesAttribute()
    {
        return EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1);
        })->get();
    }

    public function getTimelineScoreAttribute()
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q)  {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('employees.status', '=', 1);
        })->get();
        if ($review->sum('agreed_rating') > 0){
            $data = (@$review->average('agreed_rating') / @$review->sum('agreed_rating') * 100);
        }
        else
            $data = 0;
        return round($data);
    }


    public function employee_timeline_completed_activities($employee)
    {
        return EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) use($employee) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.id', '=', $employee);
        })->get();
    }



    public function department_timeline_completed_activities($department)
    {
        return EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) use($department) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', '=', $department);
        })->get();
    }



    public function department_timeline_score($department)
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q) use($department) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', '=', $department);
        })->get();
        if ($review->sum('agreed_rating') > 0){
            $data = (@$review->average('agreed_rating') / @$review->sum('agreed_rating') * 100);
        }
        else
            $data = 0;
        return round($data);
    }


    public function employee_timeline_score($employee)
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q) use($employee) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.'.$this->title, '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.employee_id', '=', $employee);
        })->get();
        if ($review->sum('agreed_rating') > 0){
            $data = (@$review->average('agreed_rating') / @$review->sum('agreed_rating') * 100);
        }
        else
            $data = 0;
        return round($data);
    }

    public function employeeTimelineScore($employee, $timeline)
    {
      return  GeneralHelper::bscTimelineScore($employee, $timeline, session('current_company_year'));
    }

    public function lastYearEmployeeTimelineScore($employee, $timeline)
    {
        $company = Company::find(session('current_company'));
        $year = CompanyYear::where('id', '<', session('current_company_year'))->where('group_id', '=', $company->sector->group_id)->orderBy('id', 'DESC')->first('id');
      return  GeneralHelper::bscTimelineScore($employee, $timeline, $year->id);
    }


}
