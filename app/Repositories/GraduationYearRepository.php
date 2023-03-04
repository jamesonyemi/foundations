<?php

namespace App\Repositories;

interface GraduationYearRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
