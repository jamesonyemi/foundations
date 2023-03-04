<?php

namespace App\Repositories;

interface ExamAttendanceRepository
{
    public function getAll();

    public function getAllForStudents($student_ids);
}
