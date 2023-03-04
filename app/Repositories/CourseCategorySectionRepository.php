<?php

namespace App\Repositories;

interface CourseCategorySectionRepository
{
    public function getAll();

    public function getAllForCourseCategory($category_id);
}
