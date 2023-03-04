<?php

namespace App\Repositories;

interface TimetablePeriodRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
