<?php

namespace App\Repositories;

use App\Models\Programme;

class ProgrammeRepositoryEloquent implements ProgrammeRepository
{
    /**
     * @var Programme
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param Programme $model
     */
    public function __construct(Programme $model)
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
