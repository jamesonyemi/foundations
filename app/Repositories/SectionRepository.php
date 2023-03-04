<?php

namespace App\Repositories;

interface SectionRepository
{
    public function getAll();

    public function getAllForSchoolYear($company_year_id);

    public function getAllForSchoolYearSchool($company_year_id, $company_id);

    public function getAllForSchoolYearSchoolChart($school_year_id, $company_id);

    public function getAllForSchool($company_id);

    public function getAllForSchoolYearSchoolAndHeadTeacher($company_year_id, $company_id, $user_id);
}
