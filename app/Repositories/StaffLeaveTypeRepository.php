<?php

namespace App\Repositories;

interface StaffLeaveTypeRepository
{
    public function getAll();

    public function getAllForSchool($company_id);
}
