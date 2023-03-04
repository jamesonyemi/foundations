<?php

namespace App\Repositories;

use App\Models\CourseCategorySection;
use App\Models\Department;

class CourseCategorySectionRepositoryEloquent implements CourseCategorySectionRepository
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
    public function __construct(CourseCategorySection $model)
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
