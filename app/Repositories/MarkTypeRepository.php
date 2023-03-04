<?php

namespace App\Repositories;

interface MarkTypeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
