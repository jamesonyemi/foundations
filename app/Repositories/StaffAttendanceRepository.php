<?php

namespace App\Repositories;

interface StaffAttendanceRepository
{
    public function getAllForSchool($company_id);

    public function getAllForSchoolYear($company_year_id);

    public function getAllForSchoolSchoolYear($company_id, $company_year_id);

    public function getAllForSchoolSchoolYearStaff($company_id, $company_year_id, $staff_id);
}
