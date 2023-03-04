<?php

namespace App\Repositories;

interface SalaryRepository
{
    public function getAll();

    public function getAllForSchoolYearSchool($company_id, $company_year_id);

    public function getAllForSchoolMonthAndYear($company_id, $month, $year);

    public function getAllForMonthAndYear($month, $year);

    public function getAllForSchoolYear($company_year_id);
}
