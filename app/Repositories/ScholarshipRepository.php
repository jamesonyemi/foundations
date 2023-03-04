<?php

namespace App\Repositories;

interface ScholarshipRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
