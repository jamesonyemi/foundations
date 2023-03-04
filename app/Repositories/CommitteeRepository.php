<?php

namespace App\Repositories;

interface CommitteeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSection($section_id);
}
