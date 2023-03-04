<?php

namespace App\Repositories;

interface SessionAttendanceRepository
{
    public function getAll();

    public function getAllStudentsForSchool($center_id, $school_year_id);

    public function getAllStudentsForSchoolSession($company_id, $school_year_id, $session_id);

    public function getAllStudentsForSchoolSessionMale($company_id, $school_year_id, $session_id);

    public function getAllStudentsForSchoolSessionFemale($company_id, $school_year_id, $session_id);
}
