<?php

namespace App\Repositories;

use App\Models\Religion;

class ReligionRepositoryEloquent implements ReligionRepository
{
    /**
     * @var Religion
     */
    private $model;

    /**
     * MaritalStatusRepositoryEloquent constructor.
     * @param Religion $model
     */
    public function __construct(Religion $model)
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
