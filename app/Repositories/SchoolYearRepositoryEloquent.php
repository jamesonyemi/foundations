<?php

namespace App\Repositories;

use App\Models\CompanyYear;

class SchoolYearRepositoryEloquent implements SchoolYearRepository
{
    /**
     * @var CompanyYear
     */
    private $model;

    /**
     * SchoolYearRepositoryEloquent constructor.
     * @param CompanyYear $model
     */
    public function __construct(CompanyYear $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model;
    }
}
