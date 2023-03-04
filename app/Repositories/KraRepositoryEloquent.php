<?php

namespace App\Repositories;

use App\Models\Kra;

class KraRepositoryEloquent implements KraRepository
{
    /**
     * @var Kra
     */
    private $model;

    /**
     * LevelRepositoryEloquent constructor.
     * @param Kra $model
     */
    public function __construct(Kra $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_year_id', session('current_company_year'));
    }

    /*
    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }*/

    public function getAllForSchoolYearSchool($company_id, $year_id, $perspective_id)
    {
        return $this->model
            /*->where('company_id', $company_id)*/
            ->where('bsc_perspective_id', $perspective_id)
            ->where('company_year_id', $year_id);
    }

    public function getAllForSchoolYearSchoolKpi($company_id, $year_id)
    {
        return $this->model
            /*->where('company_id', $company_id)*/
            ->where('company_year_id', $year_id);
    }

    public function getAllForSection($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }
}
