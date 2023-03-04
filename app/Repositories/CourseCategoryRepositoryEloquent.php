<?php

namespace App\Repositories;

use App\Models\CourseCategory;

class CourseCategoryRepositoryEloquent implements CourseCategoryRepository
{
    /**
     * @var CourseCategory
     */
    private $model;

    /**
     * LevelRepositoryEloquent constructor.
     * @param Level $model
     */
    public function __construct(CourseCategory $model)
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
