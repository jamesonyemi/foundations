<?php

namespace App\Repositories;

interface ApplicantDocRepository
{
    public function getAll();

    public function getAllForApplicant($user_id);
}
