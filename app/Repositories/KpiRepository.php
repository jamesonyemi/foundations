<?php

namespace App\Repositories;

interface KpiRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getUnapprovedForSchool($company_id, $employee_id);

    public function getAllForEmployee($company_id, $employee_id);

    public function getAllForSchoolYearSchoolEmployee($company_id, $year_id, $employee_id, $perspective_id);

    public function getAllForSection($section_id);
}
