<?php

namespace App\Repositories;

interface SchoolDirectionRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
