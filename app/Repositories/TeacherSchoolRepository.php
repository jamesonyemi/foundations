<?php

namespace App\Repositories;

interface TeacherSchoolRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function create(array $data, $activate = true);
}
