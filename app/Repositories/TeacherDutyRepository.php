<?php

namespace App\Repositories;

interface TeacherDutyRepository
{
    public function getAll();

    public function getAllForTeacher($user_id);

    public function getAllForWeek($date_start, $date_end);
}
