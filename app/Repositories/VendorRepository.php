<?php

namespace App\Repositories;

interface VendorRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
