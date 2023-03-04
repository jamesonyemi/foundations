<?php

namespace App\Models;

use App\Helpers\GeneralHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Sector extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function setSchoolYearIdAttribute($company_year_id)
    {
        $this->attributes['company_year_id'] = ($company_year_id != '') ? $company_year_id : session('current_company_year');
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class);
    }




    public function employees()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1);
    }



    public function male()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');
    }




    public function female()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');
    }


    public function exparts()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->whereIn('employees.employee_posting_group_id', [5,6])
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');
    }


    public function maleExparts()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 1)
            ->whereIn('employees.employee_posting_group_id', [5,6])
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');
    }


    public function femaleExparts()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 0)
            ->whereIn('employees.employee_posting_group_id', [5,6])
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');
    }



    public function retiring()
    {
        return $this->hasManyThrough(Employee::class, Company::class)->where('employees.status', 1)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->whereYear('users.birth_date', now()->subYears(59)->year);
    }



    public function companies()
    {
        return $this->hasMany(Company::class)->where('active', 'Yes');
    }


    public function getMaleEmployeesAttribute()
    {
        return Employee::whereHas('school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();
    }


    public function getFemaleEmployeesAttribute()
    {
        return Employee::whereHas('school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();
    }




    public function getKpisAttribute()
    {
        return Kpi::whereHas('employee.school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->get();
    }







    public function kpis()
    {
        return Kpi::whereHas('employee.school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->get();
    }


    /*public function kpiSignOffs()
    {
        return $this->hasManyThrough(EmployeeKpiSignOff::class, Employee::class, 'company_id', 'employee_id')->whereHas('employee.school',  function ($q)  {
        $q->where('companies.sector_id', $this->id);
        })->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
            ->where('employees.status', '=', 1)
            ->where('employee_kpi_sign_offs.status', '=', 1);
    }*/

    public function getKpiSignOffsAttribute()
    {
        return Employee::whereHas('school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('kpiSignOffs',  function ($q)  {
                $q->where('employee_kpi_sign_offs.status', '=', 1);
            });
    }


    public function bscSelfReview($timelineId)
    {
        return Employee::whereHas('school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timelineId)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timelineId)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.self_rating');
            });
    }


    public function bscSupervisorReview($timelineId)
    {
        return Employee::whereHas('school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->where('status',1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timelineId)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timelineId)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.agreed_rating');
            });
    }


    public function getNoBscAttribute()
    {
        return Employee::whereHas('school',  function ($q)  {
                $q->where('companies.sector_id', $this->id);
            })->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs');
    }



    public function getPendingKpiSignOffsAttribute()
    {
        return Employee::whereHas('school',  function ($q)  {
            $q->where('companies.sector_id', $this->id);
        })->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('kpiSignOffs',  function ($q)  {
                $q->where('employee_kpi_sign_offs.status', '=', 0);
            });
    }

    public function getKpiScoreAttribute()
    {
        return @GeneralHelper::sector_total_score($this->id);
    }

    public function bscScore()
    {
        $data = @GeneralHelper::sector_total_score($this->id);
        $score = @number_format(@$data,2);
        return @$score;

    }

    public function getKpiActivitiesAttribute()
    {
        $kpiactivities = EmployeeKpiActivity::whereHas('kpi.employee.school', function ($q) {
            $q->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('companies.sector_id', $this->id);
        })->get();

        return $kpiactivities;
    }


    public function getCompletedKpiActivitiesAttribute()
    {
        $kpiactivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee.school', function ($q) {
            $q->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('companies.sector_id', $this->id);
        })->get();

        return $kpiactivities;
    }



    public function getTotalOutstandingLeaveDaysAttribute()
    {
        return  Employee::where('status', 1)->whereHas('company', function ($q) {
            $q->where('companies.active', 'Yes')
                ->where('companies.sector_id', session('current_company_sector'));
        })->sum('outstanding_leave_days');
    }

    public function getTotalLeaveDaysAttribute()
    {
        return  Employee::where('status', 1)->join('positions', 'positions.id', 'employees.position_id')->whereHas('company', function ($q) {
            $q->where('companies.active', 'Yes')
                ->where('companies.sector_id', session('current_company_sector'));
        })->sum('positions.leave_days');
    }

    public function getLeaveApplicationsAttribute()
    {
        return  StaffLeave::where('approved', 1)->where('company_year_id', session('current_company_year'))->whereHas('employee.company', function ($q) {
            $q->where('companies.active', 'Yes')
                ->where('companies.sector_id', session('current_company_sector'));
        })->sum('days');
    }

    public function revenue()
    {
        return $this->hasManyThrough(Gl_entry::class, Company::class)->where('reversed', 0)
            ->where('companies.active', 'Yes')
            ->whereBetween('gl_account_no', [40000, 49999]);
    }



    public function expense($year, $month)
    {
        return $this->hasManyThrough(Gl_entry::class, Company::class)->where('reversed', 0)
            ->where('companies.active', 'Yes')
            ->whereBetween('gl_account_no', [50210, 81520])
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month);;
    }


    public function inventory($year, $month)
    {
        return $this->hasManyThrough(Gl_entry::class, Company::class)->where('reversed', 0)
            ->where('companies.active', 'Yes')
            ->whereYear('posting_date', $year)
            ->whereMonth('posting_date', $month)
            ->whereBetween('gl_account_no', [13007, 13065])
            ->whereBetween('gl_account_no', [13091, 13093]);
    }

    public function payables($year, $month)
    {
        return $this->hasManyThrough(Gl_entry::class, Company::class)->where('reversed', 0)
            ->where('companies.active', 'Yes')
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
        return $this->hasManyThrough(Gl_entry::class, Company::class)->where('reversed', 0)
            ->where('companies.active', 'Yes')
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
