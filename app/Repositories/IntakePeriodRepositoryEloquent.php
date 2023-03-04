<?php

namespace App\Repositories;

use App\Models\IntakePeriod;

class IntakePeriodRepositoryEloquent implements IntakePeriodRepository
{
    /**
     * @var IntakePeriod
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param IntakePeriod $model
     */
    public function __construct(IntakePeriod $model)
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
