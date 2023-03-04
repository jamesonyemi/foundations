<?php

namespace App\Repositories;

interface NoticeRepository
{
    public function getAll();

    public function getAllForSchoolYearAndSchool($company_year_id, $company_id);

    public function getAllForSchoolYearAndGroup($company_year_id, $student_group, $user_id);
}
