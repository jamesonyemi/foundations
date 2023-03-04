<?php

namespace App\Repositories;

interface AccountRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
