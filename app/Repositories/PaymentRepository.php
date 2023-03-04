<?php

namespace App\Repositories;

interface PaymentRepository
{
    public function getAll();

    public function getAllStudentsForSchool($company_id, $year_id, $semester_id);
}
