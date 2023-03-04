<?php

namespace App\Repositories;

interface FeesStatusRepository
{
    public function getAll();

    public function getAllStudentsForSchool($company_id);

    public function getAllDebtor();

    public function getAllDebtorStudentsForSchool($company_id);
}
