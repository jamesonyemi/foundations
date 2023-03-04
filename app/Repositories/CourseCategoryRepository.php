<?php

namespace App\Repositories;

interface CourseCategoryRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
