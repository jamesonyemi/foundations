<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BscPerspective extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'bsc_perspectives';




    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('group', function (Builder $builder) {

            $company = Company::find(session('current_company'));
            if (isset($company))
            {
                if ($company->stand_alone == 1) {
                    $builder->where('company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('company.sector', function ($q) use ($company) {
                        $q->where('sectors.group_id', $company->sector->group_id);;
                    });
                }
            }

        });
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function kras()
    {
        return $this->hasMany(Kra::class);
    }



    public function getKpisAttribute($employee_id)
    {
        return KpiResponsibility::where('responsible_employee_id', $employee_id)->whereHas('kpi.kpiObjective.kra', function ($q) use ($employee_id) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', $this->id);
        })->orwhereHas('kpi.kpiObjective.kra', function ($q) use ($employee_id) {
            $q->where('kpis.company_id', session('current_company'))->Where('kpis.employee_id', $employee_id)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', $this->id);
        })->get()->unique('kpi_id');

    }



    public function getTimelineKpisAttribute($employee_id, $timeline_id)
    {
        $kpiTimeLine = KpiTimeline::find($timeline_id);
        return $kpiTimeLine->timeLineKpis($kpiTimeLine->id, $this->id, $employee_id);

    }

    public function getGroupKpisAttribute()
    {
        $company = Company::whereId(session('current_company'))->with('sector')->first();

        return Kpi::whereHas('kpiObjective.kra.company.sector', function ($q) use($company) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('sectors.group_id', $company->sector->group_id)
                ->where('kras.bsc_perspective_id', $this->id);
                })->get();

    }


    public function getGroupWeightAttribute()
    {
        $company = Company::whereId(session('current_company'))->with('sector')->first();


        return KpiResponsibility::whereHas('kpi.kpiObjective.kra.company.sector', function ($q) use ($company) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', $this->id)
                ->where('sectors.group_id', $company->sector->group_id);
        })->sum('weight');



    }

    public function getGroupActivitiesAttribute()
    {
        $company = Company::whereId(session('current_company'))->with('sector')->first();


        return EmployeeKpiActivity::whereHas('kpi.kpiObjective.kra.company.sector', function ($q) use ($company) {
            $q->where('kpis.company_year_id', '=', session('current_company_year'))
                ->where('kpis.approved', '=', 1)
                ->where('kras.bsc_perspective_id', $this->id)
                ->where('sectors.group_id', $company->sector->group_id);;
        });



    }


    public function sector_kpis($sector_id)
    {
            return Kpi::whereHas('employee.company', function ($q) use($sector_id) {
                $q->where('kpis.company_year_id', session('current_company_year'))
                    ->where('companies.sector_id', $sector_id);
            })->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.bsc_perspective_id', $this->id);
        })->get();

    }


    public function getCompanyKpisAttribute($company_id)
    {
        return Kpi::whereHas('kpiObjective.kra', function ($q) use ($company_id) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', $this->id)
                ->where('kras.company_id', $company_id);
                })->get();

    }
    public function getPerspectiveScoreAttribute($employee_id)
    {
        $data =  EmployeeKpiScore::where('employee_id', $employee_id)->where('company_year_id', session('current_company_year'))->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kras.bsc_perspective_id', $this->id);
        })->get()->sum('score');

        return number_format($data, 2);

    }



}
