<?php

namespace App\Repositories;

use App\Models\Salary;

class SalaryRepositoryEloquent implements SalaryRepository
{
    /**
     * @var Salary
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param Salary $model
     */
    public function __construct(Salary $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYearSchool($company_id, $company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id)->where('company_id', $company_id);
    }

    public function getAllForSchoolMonthAndYear($company_id, $month, $year)
    {
        return $this->model->where('company_id', $company_id)
            ->where('date', 'LIKE', $year.'-'.$month.'%');
    }

    public function getAllForMonthAndYear($month, $year)
    {
        return $this->model->where('date', 'LIKE', $year.'-'.$month.'%');
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id);
    }
}
