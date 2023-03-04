<?php

namespace App\Repositories;

interface JournalRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
