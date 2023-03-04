<?php

namespace App\Repositories;

interface CampusRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
