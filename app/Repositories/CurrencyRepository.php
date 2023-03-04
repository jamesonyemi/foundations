<?php

namespace App\Repositories;

interface CurrencyRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
