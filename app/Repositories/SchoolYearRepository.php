<?php

namespace App\Repositories;

interface SchoolYearRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
