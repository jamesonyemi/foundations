<?php

namespace App\Repositories;

interface FeesPeriodRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
