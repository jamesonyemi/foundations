<?php

namespace App\Repositories;

interface TimetableRepository
{
    public function getAll();

    public function getAllForTeacherSubject($teacher_subject_ids);

    public function getAllForSchool($company_id);
}
