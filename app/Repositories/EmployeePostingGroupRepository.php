<?php

namespace App\Repositories;

use App\Models\EmployeePostingGroup;
use App\Models\StudentPostingGroup;

class EmployeePostingGroupRepository
{
    /**
     * @var EmployeePostingGroup
     */
    private $model;

    /**
     * AccountRepositoryEloquent constructor.
     * @param EmployeePostingGroup $model
     */
    public function __construct(EmployeePostingGroup $model)
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
}
