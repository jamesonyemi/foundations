<?php

namespace App\Repositories;

interface ConferenceDayRepository
{
    public function getAll();

    public function getAllForSchoolYear($school_year_id);

    public function getAllForSchoolYearSchool($school_year_id, $company_id);

    public function getAllForSchoolYearSchoolAndHeadTeacher($school_year_id, $company_id, $user_id);

    public function getAllForSchoolYearSchoolAndSession($school_year_id, $company_id, $session_id);

    public function getAllSession($session_id);
}
