<?php

namespace App\Repositories;

use App\Models\MaritalStatus;

class MaritalStatusRepositoryEloquent implements MaritalStatusRepository
{
    /**
     * @var MaritalStatus
     */
    private $model;

    /**
     * MaritalStatusRepositoryEloquent constructor.
     * @param MaritalStatus $model
     */
    public function __construct(MaritalStatus $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    /*public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }*/
}
