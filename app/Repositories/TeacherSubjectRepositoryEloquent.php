<?php

namespace App\Repositories;

use App\Models\TeacherSubject;

class TeacherSubjectRepositoryEloquent implements TeacherSubjectRepository
{
    /**
     * @var TeacherSubject
     */
    private $model;


    /**
     * TimetableRepositoryEloquent constructor.
     * @param TeacherSubject $model
     */
    public function __construct(TeacherSubject $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForGroup($group_id)
    {
        return $this->model->where('student_group_id', $group_id)
            ->distinct();
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id)
            ->distinct();
    }

    public function getAllForSubjectAndGroup($teacher_id, $school_semester_id)
    {
        return $this->model->where('teacher_id', $teacher_id)
            ->where('semester_id', $school_semester_id)
            ->distinct();
    }

    public function getAllForSubjectAndDirection($subject_id, $direction_id, $school_semester_id)
    {
        return $this->model->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('teacher_subjects.subject_id', $subject_id)/*
            ->where('teacher_subjects.semester_id', $school_semester_id)*/
            ->where('subjects.direction_id', $direction_id)
            ->distinct();

        /*return $this->model->Has('alumni', '=', 0)
            ->whereHas('active', function ($q) use ($school_year_id, $company_id, $section_id) {
                $q->where('students.section_id', $section_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_statuses.school_year_id', $school_year_id);
            });*/
    }
/*
    public function getAllForTeacherSemester($subject_id, $group_id, $school_semester_id)
    {
        return $this->model->where('student_group_id', $group_id)
            ->where('subject_id', $subject_id)
            ->where('semester_id', $school_semester_id)
            ->distinct();
    }*/

    public function getAllForSchoolYearAndGroup($school_year_id, $group_id)
    {
        return $this->model->where('school_year_id', $school_year_id)
            ->where('student_group_id', $group_id)
            ->distinct();
    }

    public function getAllForSchoolYearAndDirection($school_year_id, $direction_id)
    {
        return $this->model->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('teacher_subjects.school_year_id', $school_year_id)
            ->where('subjects.direction_id', $direction_id)
            ->distinct();
    }

    public function getAllTeacherSubjectsForSchoolYearAndGroup($school_year_id, $student_group_id)
    {
        return $this->model->where('teacher_subjects.school_year_id', $school_year_id)
            ->where('teacher_subjects.student_group_id', $student_group_id);
    }

    public function getAllForSchoolYearAndGroups($school_year_id, $student_group_ids)
    {
        return $this->model->where('teacher_subjects.school_year_id', $school_year_id)
            ->whereIn('teacher_subjects.student_group_id', $student_group_ids);
    }

    public function getAllForSchoolYearAndDirections($school_year_id, $student_group_ids)
    {
        return $this->model->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('teacher_subjects.school_year_id', $school_year_id)
            ->whereIn('subjects.direction_id', $student_group_ids);
    }

    public function getAllForSchoolYear($school_year_id)
    {
        return $this->model->where('teacher_subjects.school_year_id', $school_year_id);
    }

    public function getAllForSchoolYearAndGroupAndTeacher($school_year_id, $group_id, $user_id)
    {
        return $this->model->where('teacher_subjects.school_year_id', $school_year_id)
            ->where('teacher_subjects.student_group_id', $group_id)
            ->where('teacher_subjects.teacher_id', $user_id);
    }

    public function getAllForSchoolYearAndTeacher($school_year_id, $company_id, $semester_id, $user_id)
    {
        return $this->model->where('teacher_subjects.school_year_id', $school_year_id)
            ->where('teacher_subjects.company_id', $company_id)
//            ->where('teacher_subjects.semester_id', $semester_id)
            ->where('teacher_subjects.teacher_id', $user_id);
    }

    public function getAllForSchoolYearAndSchool($school_year_id, $company_id)
    {
        return $this->model->where('school_year_id', $school_year_id)
            ->where('company_id', $company_id)
            ->distinct();
    }
}
