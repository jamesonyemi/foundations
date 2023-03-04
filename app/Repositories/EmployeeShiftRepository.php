<?php

namespace App\Repositories;

interface EmployeeShiftRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSchoolYear($company_id, $company_year_id);

    public function getAllForSchoolDay($company_id, $date);

    public function getAllForSchoolDepartmentDay($company_id, $section_id, $date);

    public function getAllMine($employee_id);

    public function getForEmployee($employee_id, $date);
}
