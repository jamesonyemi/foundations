<?php

namespace App\Repositories;

use App\Models\StaffSalary;

class StaffSalaryRepositoryEloquent implements StaffSalaryRepository
{
    /**
     * @var StaffSalary
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param StaffSalary $model
     */
    public function __construct(StaffSalary $model)
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

    public function getAllForSchoolAndStaff($company_id, $user_id)
    {
        return $this->model->where('company_id', $company_id)->where('user_id', $user_id);
    }
}
