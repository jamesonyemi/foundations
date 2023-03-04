<?php

namespace App\Repositories;

interface OptionRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
