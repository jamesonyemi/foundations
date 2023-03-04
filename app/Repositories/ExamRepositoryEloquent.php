<?php

namespace App\Repositories;

use App\Models\Exam;

class ExamRepositoryEloquent implements ExamRepository
{
    /**
     * @var Exam
     */
    private $model;

    /**
     * ExamRepositoryEloquent constructor.
     * @param Exam $model
     */
    public function __construct(Exam $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForGroup($student_group_id)
    {
        return $this->model->where('student_group_id', $student_group_id);
    }

    public function getAllForGroupAndSubject($student_group_id, $subject_id)
    {
        return $this->model->where('student_group_id', $student_group_id)->where('subject_id', $subject_id);
    }

    public function getAllForAdmin($user_id)
    {
        return $this->model->where('user_id', $user_id)
                           ->whereNull('parent_id');
    }
}
