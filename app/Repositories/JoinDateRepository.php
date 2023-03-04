<?php

namespace App\Repositories;

interface JoinDateRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSchoolAndStaff($company_id, $user_id);
}
