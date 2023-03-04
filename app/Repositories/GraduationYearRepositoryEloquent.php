<?php

namespace App\Repositories;

use App\Models\GraduationYear;

class GraduationYearRepositoryEloquent implements GraduationYearRepository
{
    /**
     * @var SchoolYear
     */
    private $model;

    /**
     * SchoolYearRepositoryEloquent constructor.
     * @param SchoolYear $model
     */
    public function __construct(GraduationYear $model)
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
