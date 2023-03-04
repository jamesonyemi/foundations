<?php

namespace App\Repositories;

interface StudentGroupRepository
{
    public function getAll();

    public function getAllForSchoolYearSchool($company_year_id, $company_id);

    public function getAllForSection($section_id);
}
