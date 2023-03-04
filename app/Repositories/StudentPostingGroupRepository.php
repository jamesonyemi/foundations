<?php

namespace App\Repositories;

use App\Models\StudentPostingGroup;

class StudentPostingGroupRepository
{
    /**
     * @var StudentPostingGroup
     */
    private $model;

    /**
     * AccountRepositoryEloquent constructor.
     * @param Account $model
     */
    public function __construct(StudentPostingGroup $model)
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
