<?php

namespace App\Repositories;

interface StudyMaterialRepository
{
    public function getAll();

    public function getAllForUser($user_id);

    public function getAllForUserAndGroup($user_id, $student_group_id);

    public function getAllForStudent($student_user_id);
}
