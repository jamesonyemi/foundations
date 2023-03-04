<?php

namespace App\Repositories;

use App\Models\TimetablePeriod;

class TimetablePeriodRepositoryEloquent implements TimetablePeriodRepository
{
    /**
     * @var TimetablePeriod
     */
    private $model;

    /**
     * TimetablePeriodRepositoryEloquent constructor.
     * @param TimetablePeriod $model
     */
    public function __construct(TimetablePeriod $model)
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
