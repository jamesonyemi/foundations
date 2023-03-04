<?php

namespace App\Repositories;

interface EntryModeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
