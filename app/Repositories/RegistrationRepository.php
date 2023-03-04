<?php

namespace App\Repositories;

interface RegistrationRepository
{
    public function getAll();

    public function getAllStudentsForSchool($company_id, $year_id, $semester_id);

    public function getAllStudentsForSemesterSubject($company_id, $year_id, $semester_id, $subject_id);

    public function getAllForStudent($user_id);

    public function getAllRegistrationFilter($request);
}
