<?php

namespace App\Models;

use App\Helpers\GeneralHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }


    public function schoolYears()
    {
        return $this->hasMany(CompanyYear::class, 'company_id')->where('active', 1);
    }


    public function employees()
    {
        return $this->hasMany(Employee::class);
    }


    public function hr_head()
    {
        return $this->hasOne(Employee::class, 'id' , 'hr_head_employee_id');
    }

    public function activeEmployees()
    {
        return $this->hasMany(Employee::class)->where('employees.status', 1)->whereHas('user');
    }

    public function employeesNoYearKpis()
    {
        return $this->hasMany(Employee::class)->where('employees.status', 1)->whereHas('user')->Has('yearKpis', '=', 0);
    }

    public function kpiSignOffs()
    {
        return $this->hasManyThrough(EmployeeKpiSignOff::class, Employee::class, 'company_id', 'employee_id')
            ->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
            ->where('employees.status', '=', 1)
            ->where('employee_kpi_sign_offs.status', '=', 1);
    }



    public function pendingKpiSignOffs()
    {
        return $this->hasManyThrough(EmployeeKpiSignOff::class, Employee::class, 'company_id', 'employee_id')
            ->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
            ->where('employees.status', '=', 1)
            ->where('employee_kpi_sign_offs.status', '=', 0);
    }

    public function NoBSC()
    {
        return $this->hasMany(Employee::class)
            ->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs');
    }



    public function bscSelfReview($timelineId)
    {
        return $this->hasMany(Employee::class)
            ->where('status',1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timelineId)  {
            $q->where('kpi_performance_reviews.kpi_timeline_id', $timelineId)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->whereNotNull('kpi_performance_reviews.self_rating');
        });
    }



    public function bscSupervisorReview($timelineId)
    {
        return $this->hasMany(Employee::class)
            ->where('status',1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timelineId)  {
            $q->where('kpi_performance_reviews.kpi_timeline_id', $timelineId)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->whereNotNull('kpi_performance_reviews.agreed_rating');
        });
    }


    public function getBscScoreAttribute()
    {
        $data = @GeneralHelper::company_total_score($this->id);
        $score = @number_format(@$data,2);
        return @$score;

    }


    public function bscScore()
    {
        $data = @GeneralHelper::company_total_score($this->id);
        $score = @number_format(@$data,2);
        return @$score;

    }




    public function getMaleEmployeesAttribute()
    {
        return Employee::whereHas('company',  function ($q)  {
            $q->where('companies.id', $this->id);
        })->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();
    }


    public function getCurrentYearAttribute()
    {
        $year =  CompanyYear::where('active', 1)
            ->where('company_id', $this->id)
            ->first()
            ->id;
        if (is_null($year))
        {
            $year =  CompanyYear::where('active', 1)
                ->where('group_id', $this->sector->group_id)
                ->first()
                ->id;
        }

        return $year;
    }


    public function getFemaleEmployeesAttribute()
    {
        return Employee::whereHas('company',  function ($q)  {
            $q->where('companies.id', $this->id);
        })->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();
    }


    public function getSmsMessagesYearAttribute()
    {
        return $this->sms_messages()
            ->where('created_at', 'LIKE', Carbon::now()->format('Y') . '%')->count();
    }

    public function subscriptionPlan()
    {
        return $this->hasOne(Plan::class, 'id', 'subscription_plan_id');
    }



    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }



    public function kpis()
    {
        return $this->hasManyThrough(Kpi::class, Employee::class, 'company_id', 'employee_id')
            ->where('kpis.company_year_id', session('current_company_year'))
            ->where('employees.status', '=', 1);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }


    public function procurmentPlans()
    {
        return $this->hasMany(ProcurementPlan::class)->where('company_year_id', session('current_company_year'))->orderByDesc('id');
    }


    public function getKpiScoreAttribute()
    {
        return @GeneralHelper::sector_total_score($this->id);
    }


    public function getKpiActivitiesAttribute()
    {
        $kpiactivities = EmployeeKpiActivity::whereHas('kpi.employee.company', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('companies.sector_id', $this->id);
        })->get();

        return $kpiactivities;
    }


    public function getCompletedKpiActivitiesAttribute()
    {
        $kpiactivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee.company', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('companies.sector_id', $this->id);
        })->get();

        return $kpiactivities;
    }


    public function getTotalOutstandingLeaveDaysAttribute()
    {
        return $this->hasMany(Employee::class)
            ->where('status', 1)
            ->sum('outstanding_leave_days');
    }

    public function getTotalLeaveDaysAttribute()
    {
        return $this->hasMany(Employee::class)
            ->where('employees.status', 1)
            ->join('positions', 'positions.id', 'employees.position_id')
            ->sum('positions.leave_days');
    }

    public function getLeaveApplicationsAttribute()
    {
        return $this->hasManyThrough(StaffLeave::class, Employee::class, 'company_id', 'employee_id')
            ->where('approved', 1)
            ->where('company_year_id', session('current_company_year'))
            ->where('employees.status', '=', 1)
            ->sum('days');
    }



    public function revenue()
    {
        return $this->hasMany(Gl_entry::class)->where('reversed', 0)
            ->whereBetween('gl_account_no', [40000, 49999]);
    }



    public function expense($year, $month)
    {
        return $this->hasMany(Gl_entry::class)->where('reversed', 0)
            ->whereBetween('gl_account_no', [50210, 81520])
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month);;
    }


    public function inventory($year, $month)
    {
        return $this->hasMany(Gl_entry::class)->where('reversed', 0)
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month)
            ->whereBetween('gl_account_no', [13007, 13065])
            ->whereBetween('gl_account_no', [13091, 13093]);
    }

    public function payables($year, $month)
    {
        return $this->hasMany(Gl_entry::class)->where('reversed', 0)
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month)

            //VENDORS
            ->whereBetween('gl_account_no', [20110, 20140])
            ->whereBetween('gl_account_no', [20210, 20240])
            ->whereBetween('gl_account_no', [20310, 20320])

            //STATUTORY
            ->whereBetween('gl_account_no', [12210, 12250])
            ->whereBetween('gl_account_no', [20410, 20430])
            ->whereBetween('gl_account_no', [21010, 21095])
            ->whereBetween('gl_account_no', [21105, 21170])
            ->whereBetween('gl_account_no', [22110, 22150]);
    }

    public function receivables($year, $month)
    {
        return $this->hasMany(Gl_entry::class)->where('reversed', 0)
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month)
            ->whereBetween('gl_account_no', [13007, 13065])
            ->whereBetween('gl_account_no', [13091, 13093]);
    }

    public function monthlyRevenue($year, $month)
    {
        return $this->revenue()
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month);
    }





}
