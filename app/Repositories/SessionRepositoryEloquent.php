<?php

namespace App\Repositories;

use App\Models\Session;

class SessionRepositoryEloquent implements SessionRepository
{
    /**
     * @var Session
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param Session $model
     */
    public function __construct(Session $model)
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
