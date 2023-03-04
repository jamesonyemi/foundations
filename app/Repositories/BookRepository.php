<?php

namespace App\Repositories;

interface BookRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
