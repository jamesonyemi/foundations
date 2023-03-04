<?php

namespace App\Repositories;

interface MarkValueRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSubject($subject_id);

    public function getAllMarkValueOptions($subject_id);
}
