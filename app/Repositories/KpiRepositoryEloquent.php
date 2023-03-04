<?php

namespace App\Repositories;

use App\Models\Kpi;
use App\Models\KpiResponsibility;

class KpiRepositoryEloquent implements KpiRepository
{
    /**
     * @var Kpi
     */
    private $model;

    /**
     * LevelRepositoryEloquent constructor.
     * @param Kpi $model
     */
    public function __construct(Kpi $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForEmployee($company_id, $employee_id)
    {
        return $this->model->where('company_id', $company_id)->where('employee_id', $employee_id)->orWhere('responsible_employee_id', $employee_id);
    }

    public function getUnapprovedForSchool($company_id, $employee_id)
    {
        return $this->model->where('company_id', $company_id)->where('supervisor_employee_id', $employee_id)->where('approved', 0);
    }

    public function getAllForSchoolYearSchoolEmployee($company_id, $year_id, $employee_id, $perspective_id)
    {
        return KpiResponsibility::where('responsible_employee_id', $employee_id)->whereHas('kpi.kpiObjective.kra', function ($q) use ($company_id, $year_id, $employee_id, $perspective_id) {
            $q->where('kpis.company_id', $company_id)
                ->where('kpis.company_year_id', $year_id)
                ->where('kras.bsc_perspective_id', $perspective_id);
        })->orWhere('owner_employee_id', $employee_id)->whereHas('kpi.kpiObjective.kra', function ($q) use ($company_id, $year_id, $employee_id, $perspective_id) {
            $q->where('kpis.company_id', $company_id)
                ->where('kpis.company_year_id', $year_id)
                ->where('kras.bsc_perspective_id', $perspective_id);
        });
    }

    /*

    public function getAllForSchoolYearSchoolEmployee($company_id, $year_id, $employee_id, $perspective_id)
    {

        return $this->model->whereHas('kpiObjective.kra', function ($q) use ($company_id, $year_id, $employee_id, $perspective_id) {
            $q->where('kpis.company_id', $company_id)
                ->where('kpis.company_year_id', $year_id)
                ->where('kpis.employee_id', $employee_id)
                ->where('kras.bsc_perspective_id', $perspective_id);
        });
    }*/

    public function getAllForSection($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }
}
