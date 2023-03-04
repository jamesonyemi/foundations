<?php

namespace App\Repositories;

interface ApplicationTypeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSection($section_id);
}
