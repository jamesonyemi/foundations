<?php

namespace App\Repositories;

use App\Models\ExamAttendance;

class ExamAttendanceRepositoryEloquent implements ExamAttendanceRepository
{
    /**
     * @var ExamAttendance
     */
    private $model;

    /**
     * ExamAttendanceRepositoryEloquent constructor.
     * @param ExamAttendance $model
     */
    public function __construct(ExamAttendance $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForStudents($student_ids)
    {
        return $this->model->whereIn('student_id', $student_ids);
    }
}
