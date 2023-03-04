<?php

namespace App\Repositories;

use App\Models\Scholarship;

class ScholarshipRepositoryEloquent implements ScholarshipRepository
{
    /**
     * @var Scholarship
     */
    private $model;

    /**
     * ScholarshipRepositoryEloquent constructor.
     * @param Scholarship $model
     */
    public function __construct(Scholarship $model)
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
