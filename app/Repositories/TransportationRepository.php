<?php

namespace App\Repositories;

interface TransportationRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForUser($user_id);
}
