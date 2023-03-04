<?php

namespace App\Repositories;

use App\Models\Semester;

class SemesterRepositoryEloquent implements SemesterRepository
{
    /**
     * @var Semester
     */
    private $model;

    /**
     * SemesterRepositoryEloquent constructor.
     * @param Semester $model
     */
    public function __construct(Semester $model)
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
