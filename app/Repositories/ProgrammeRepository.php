<?php

namespace App\Repositories;

interface ProgrammeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
