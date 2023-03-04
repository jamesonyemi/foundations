<?php

namespace App\Repositories;

interface ApplicantSchoolRepository
{
    public function getAll();

    public function getAllForApplicant($user_id);
}
