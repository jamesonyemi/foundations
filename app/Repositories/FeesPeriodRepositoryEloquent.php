<?php

namespace App\Repositories;

use App\Models\FeesPeriod;

class FeesPeriodRepositoryEloquent implements FeesPeriodRepository
{
    /**
     * @var FeesPeriod
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param FeesPeriod $model
     */
    public function __construct(FeesPeriod $model)
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
