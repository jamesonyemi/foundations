<?php

namespace App\Repositories;

interface SemesterRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
