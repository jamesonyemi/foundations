<?php

namespace App\Repositories;

interface LevelRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSection($section_id);
}
