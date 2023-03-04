<?php

namespace App\Repositories;

interface BehaviorRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
