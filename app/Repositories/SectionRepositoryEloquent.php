<?php

namespace App\Repositories;

use App\Models\Department;

class SectionRepositoryEloquent implements SectionRepository
{
    /**
     * @var Department
     */
    private $model;

    /**
     * SectionRepositoryEloquent constructor.
     *
     * @param Department $model
     */
    public function __construct(Department $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id);
    }

    public function getAllForSchoolYearSchool($company_year_id, $company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForSchoolYearSchoolChart($school_year_id, $company_id)
    {
        return $this->model->whereHas('employees');
    }

    public function getAllForSchool($company_id)
    {
        return $this->model;
    }

    public function getAllForSchoolYearSchoolAndHeadTeacher($company_year_id, $company_id, $user_id)
    {
        return $this->model->where('company_id', '=', $company_id)
                           ->where('section_teacher_id', '=', $user_id);
    }
}
