<?php

namespace App\Repositories;

interface IntakePeriodRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
