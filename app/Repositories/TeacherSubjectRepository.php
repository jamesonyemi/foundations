<?php

namespace App\Repositories;

interface TeacherSubjectRepository
{
    public function getAll();

    public function getAllForGroup($group_id);

    public function getAllForSchool($company_id);

    public function getAllForSubjectAndGroup($teacher_id, $school_semester_id);

    public function getAllForSubjectAndDirection($subject_id, $direction_id, $school_semester_id);

    public function getAllForSchoolYearAndGroup($school_year_id, $group_id);

    public function getAllForSchoolYearAndDirection($school_year_id, $direction_id);

    public function getAllForSchoolYearAndGroups($school_year_id, $student_group_ids);

    public function getAllForSchoolYearAndDirections($school_year_id, $student_group_ids);

    public function getAllForSchoolYear($school_year_id);

    public function getAllForSchoolYearAndGroupAndTeacher($school_year_id, $group_id, $user_id);

    public function getAllForSchoolYearAndTeacher($school_year_id, $company_id, $semester_id, $user_id);

    public function getAllTeacherSubjectsForSchoolYearAndGroup($school_year_id, $group_id);

    public function getAllForSchoolYearAndSchool($school_year_id, $company_id);
}
