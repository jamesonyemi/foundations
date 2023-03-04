<?php

namespace App\Repositories;

interface StaffSalaryRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSchoolAndStaff($company_id, $user_id);
}
