<?php

namespace App\Repositories;

use App\Helpers\Settings;
use App\Models\OnlineExam;
use Carbon\Carbon;

class OnlineExamRepositoryEloquent implements OnlineExamRepository
{
    /**
     * @var OnlineExam
     */
    private $model;

    /**
     * ExamRepositoryEloquent constructor.
     * @param OnlineExam $model
     */
    public function __construct(OnlineExam $model)
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

    public function getAllForStudentUserId($student_user_id)
    {
        return $this->model->join('student_student_group', 'student_student_group.student_group_id', '=', 'online_exams.student_group_id')
            ->join('students', 'students.id', '=', 'student_student_group.student_id')
            ->where('students.user_id', $student_user_id)
            ->distinct('online_exams.*')
            ->select('online_exams.*')
            ->get()
            ->filter(function ($onlineExam) {
                return ($onlineExam->date_start <=
                        Carbon::now()->format(Settings::get('date_format'))) &&
                    ($onlineExam->date_end >=
                        Carbon::now()->format(Settings::get('date_format')));
            });
    }
}
