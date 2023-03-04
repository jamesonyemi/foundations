<?php

namespace App\Repositories;

interface SchoolRepository
{
    public function getAll();

    public function getAllForGroup($group_id);

    public function getAllForSector($sector_id);

    public function getAllAdmin();

    public function getAllTeacher();

    public function getAllStudent();

    public function getAllAluministudents($company_id, $schoolYearId);

    public function getAllCanApply();

    public function getAllApplicant();
}
