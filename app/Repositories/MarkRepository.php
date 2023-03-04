<?php

namespace App\Repositories;

interface MarkRepository
{
    public function getAll();

    public function getAllForSchoolYearAndBetweenDate($company_year_id, $date_start, $date_end);

    public function getAllForSchoolYearAndExam($company_year_id, $exam_id);

    public function getAllForSchoolYearSubjectUserAndSemester($company_year_id, $subject_id, $user_id, $semester_id);

    public function getAllForSchoolYearSubjectAndSemester($company_year_id, $subject_id, $semester_id);

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids);

    public function getAllForExam($exam_id);

    public function getAllForSchoolYearStudentsAndBetweenDate($company_year_id, $student_user_ids, $start_date, $end_date);

    public function getAllForSchoolYearStudentsSubject($company_year_id, $student_user_ids, $subject_id);
}
