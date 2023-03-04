<?php

namespace App\Repositories;

interface UnionRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
