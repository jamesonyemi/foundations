<?php

namespace App\Repositories;

use App\Models\Behavior;

class BehaviorRepositoryEloquent implements BehaviorRepository
{
    /**
     * @var Behavior
     */
    private $model;

    /**
     * BehaviorRepositoryEloquent constructor.
     * @param Behavior $model
     */
    public function __construct(Behavior $model)
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
