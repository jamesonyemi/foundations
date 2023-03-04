<?php

namespace App\Repositories;

use App\Models\CourseCategoryProgram;
use App\Models\CourseCategorySection;
use App\Models\Department;

class CourseCategoryDirectionRepositoryEloquent implements CourseCategoryDirectionRepository
{
    /**
     * @var Department
     */
    private $model;

    /**
     * SectionRepositoryEloquent constructor.
     *
     * @param Department $model
     */
    public function __construct(CourseCategoryProgram $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForCourseCategory($category_id)
    {
        return $this->model->where('course_category_id', $category_id);
    }
}
