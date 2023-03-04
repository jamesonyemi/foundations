<?php

namespace App\Repositories;

interface OnlineExamRepository
{
    public function getAll();

    public function getAllForGroup($student_group_id);

    public function getAllForGroupAndSubject($student_group_id, $subject_id);

    public function getAllForStudentUserId($student_user_id);
}
