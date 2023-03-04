<?php

namespace App\Repositories;

interface StudentFinalMarkRepository
{
    public function getAll();

    public function getAllForStudentAndSchoolYear($student_id, $company_year_id);

    public function getAllForStudentSubjectSchoolYearSchool($student_id, $subject_id, $company_year_id, $company_id);

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids);
}
