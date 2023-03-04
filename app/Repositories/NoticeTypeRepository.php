<?php

namespace App\Repositories;

interface NoticeTypeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
