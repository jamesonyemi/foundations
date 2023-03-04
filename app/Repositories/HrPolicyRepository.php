<?php

namespace App\Repositories;

interface HrPolicyRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
