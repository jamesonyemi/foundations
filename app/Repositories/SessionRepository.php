<?php

namespace App\Repositories;

interface SessionRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
