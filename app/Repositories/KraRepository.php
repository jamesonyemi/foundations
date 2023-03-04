<?php

namespace App\Repositories;

interface KraRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSection($section_id);

    public function getAllForSchoolYearSchool($company_id, $year_id, $perspective_id);

    public function getAllForSchoolYearSchoolKpi($company_id, $year_id);
}
