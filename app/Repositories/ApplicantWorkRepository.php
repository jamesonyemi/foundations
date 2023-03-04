<?php

namespace App\Repositories;

interface ApplicantWorkRepository
{
    public function getAll();

    public function getAllForApplicant($user_id);
}
