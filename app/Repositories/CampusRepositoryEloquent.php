<?php

namespace App\Repositories;

use App\Models\Campus;

class CampusRepositoryEloquent implements CampusRepository
{
    /**
     * @var Campus
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param Campus $model
     */
    public function __construct(Campus $model)
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
