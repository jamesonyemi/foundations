<?php

namespace App\Repositories;

interface ExamRepository
{
    public function getAll();

    public function getAllForGroup($student_group_id);

    public function getAllForGroupAndSubject($student_group_id, $subject_id);

    public function getAllForAdmin($user_id);
}
