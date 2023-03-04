<?php

namespace App\Repositories;

interface SubjectRepository
{
    public function getAll();

    public function getAllForSchool($company_id);

    public function getAllForSchoolRegistration($company_id);

    public function getAllForDirectionAndClass($direction_id, $class);

    public function getAllForSection($section_id);

    public function getAllForDirection($section_id);

    public function getAllForStudentGroup($student_group_id);

    public function getAllStudentsSubjectAndDirection();

    public function getAllStudentsSubjectsTeacher($student_user_id, $company_year_id);

    public function create(array $data);
}
