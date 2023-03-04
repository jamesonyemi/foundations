<?php

namespace App\Repositories;

interface DiaryRepository
{
    public function getAll();

    public function getAllForSchoolYear($company_year_id);

    public function getAllForSchoolYearAndSchool($company_year_id, $company_id);

    public function getAllForSchoolYearAndStudentUserId($company_year_id, $student_user_id);
}
