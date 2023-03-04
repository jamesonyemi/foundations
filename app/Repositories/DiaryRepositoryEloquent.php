<?php

namespace App\Repositories;

use App\Models\Diary;

class DiaryRepositoryEloquent implements DiaryRepository
{
    /**
     * @var Diary
     */
    private $model;

    /**
     * DiaryRepositoryEloquent constructor.
     * @param Diary $model
     */
    public function __construct(Diary $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id);
    }

    public function getAllForSchoolYearAndStudentUserId($company_year_id, $student_user_id)
    {
        return $this->model->join('subjects', 'subjects.id', '=', 'diaries.subject_id')
            ->join('teacher_subjects', 'teacher_subjects.subject_id', '=', 'subjects.id')
            ->join('student_student_group', 'student_student_group.student_group_id', '=', 'teacher_subjects.student_group_id')
            ->join('students', 'students.id', '=', 'student_student_group.student_id')
            ->where('diaries.company_year_id', $company_year_id)
            ->where('students.user_id', $student_user_id)
            ->select('diaries.*');
    }

    public function getAllForSchoolYearAndSchool($company_year_id, $company_id)
    {
        return $this->model->where('company_year_id', $company_year_id)->where('company_id', $company_id);
    }
}
