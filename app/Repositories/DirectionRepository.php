<?php

namespace App\Repositories;

interface DirectionRepository
{
    public function getAll();

    public function getAllForSection($section_id);

    public function getAllForSchool($company_id);
}
