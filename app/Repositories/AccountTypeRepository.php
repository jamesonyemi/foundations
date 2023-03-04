<?php

namespace App\Repositories;

interface AccountTypeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
