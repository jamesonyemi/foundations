<?php

namespace App\Repositories;

interface CourseCategoryDirectionRepository
{
    public function getAll();

    public function getAllForCourseCategory($category_id);
}
