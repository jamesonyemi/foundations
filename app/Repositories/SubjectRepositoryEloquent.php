<?php

namespace App\Repositories;

use App\Models\Subject;

class SubjectRepositoryEloquent implements SubjectRepository
{
    /**
     * @var Subject
     */
    private $model;

    /**
     * SubjectRepositoryEloquent constructor.
     * @param Subject $model
     */
    public function __construct(Subject $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForSchoolRegistration($company_id)
    {
        return $this->model->where('company_id', $company_id)
            ->where('subject_show', 'on');
    }

    public function getAllForDirectionAndClass($direction_id, $class)
    {
        return $this->model->where('direction_id', $direction_id);
    }

    public function getAllForDirection($direction_id)
    {
        return $this->model->where('direction_id', $direction_id);
    }

    public function getAllForSection($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }

    public function getAllStudentsSubjectAndDirection()
    {
        return $this->model->join('directions', 'subjects.direction_id', '=', 'directions.id')
            ->join('student_groups', function ($join) {
                $join->on('student_groups.direction_id', '=', 'directions.id');
                $join->on('student_groups.class', '=', 'subjects.class');
            })
            ->join('student_student_group', 'student_student_group.student_group_id', '=', 'student_groups.id')
            ->join('students', 'students.id', '=', 'student_student_group.student_id');
    }

    public function getAllStudentsSubjectsTeacher($student_user_id, $company_year_id)
    {
        return $this->model->join('teacher_subjects', 'teacher_subjects.subject_id', '=', 'subjects.id')
            ->join('student_student_group', 'student_student_group.student_group_id', '=', 'teacher_subjects.student_group_id')
            ->join('students', 'students.id', '=', 'student_student_group.student_id')
            ->join('users', 'users.id', '=', 'teacher_subjects.teacher_id')
            ->where('students.user_id', $student_user_id)
            ->where('students.company_year_id', $company_year_id)
            ->select('users.*')
            ->distinct();
    }

    public function create(array $data)
    {
        $subject = new Subject($data);
        $subject->company_id = session('current_company');
        $subject->save();

        return $subject;
    }

    public function getAllForStudentGroup($student_group_id)
    {
        return $this->model->join('student_groups', function ($join) {
            $join->on('student_groups.direction_id', '=', 'subjects.direction_id');
            $join->on('student_groups.class', '=', 'subjects.class');
        })->where('student_groups.id', $student_group_id)
                      ->select('subjects.*')
                      ->distinct();
    }
}
