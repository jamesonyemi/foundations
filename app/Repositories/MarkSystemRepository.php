<?php

namespace App\Repositories;

interface MarkSystemRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSchoolSubject($company_id, $subject_id);
}
