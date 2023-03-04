<?php

namespace App\Repositories;

interface ApplicantRepository
{
    public function getAll();

    public function getAllMale();

    public function getAllFemale();

    public function getAllForSchoolYearAndSection($company_year_id, $section_id);

    public function getAllForSchoolYear($company_year_id);

    public function getAllActiveFilter($request);

    public function getAllActiveExport($request);

    public function getAllForSchoolYearAndSchool($company_year_id, $company_id, $school_semester_id);

    public function getAllForSchoolYearSchoolAndSection($company_year_id, $company_id, $section_id);

    public function getSchoolForStudent($student_user_id, $company_year_id);

    public function create(array $data, $activate = true);

    public function deactivate($applicant_id);

    public function getAllForSection($section_id);

    public function getAllForSection2($section_id);

    public function getAllForDirection($section_id);

    public function getAllForSectionCurrency($section_id, $currency_id);

    public function getAllForDirectionCurrency($direction_id, $currency_id);

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids);

    public function getCountStudentsForSchoolAndSchoolYear($company_id, $schoolYearId);
}
