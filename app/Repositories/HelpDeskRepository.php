<?php

namespace App\Repositories;

interface HelpDeskRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSchoolYear($company_id, $company_year_id);

    public function getAllMe($employee_id);

    public function getAllMine($employee_id);

    public function getAllOpen($company_id);

    public function getAllClosed($company_id);
}
