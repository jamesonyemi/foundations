<?php

namespace App\Repositories;

interface EmployeeRepository
{
    public function getAll();

    public function getAllMale();

    public function getAllFemale();

    public function getAllMaleConfirm();

    public function getAllFemaleConfirm();

    public function getAllMalePresent();

    public function getAllFemalePresent();

    public function getAllForSchoolYearAndSection3($school_year_id, $section_id);

    public function getAllForSchoolYearAndLevel($school_year_id, $level_id);

    public function getAllForSchoolYearAndCommittee($school_year_id, $committee_id);

    public function getAllForCommitee($school_year_id, $company_id);

    public function getAllForSchoolWithFilter($company_id, $school_year_id, $request = null);

    public function getAllForSchoolWithFilterPresent($company_id, $school_year_id, $request = null);

    public function getAllForSessionAbsent($company_id, $school_year_id, $session_id);

    public function getAllForSchoolConfirm($school_year_id, $company_id);

    public function getAllForEmployeeSubordinates($company_id, $employee_id);

    public function getAllForSchoolYearAndSection($company_year_id, $company_id, $section_id);

    public function getAllForSchoolYearAndDirection($company_year_id, $company_id, $direction_id);

    public function getAllForSchoolYear($company_year_id);

    public function getAllStudentGroupsForStudentUserAndSchoolYear($student_user_id, $company_year_id);

    public function getAllForStudentGroup($student_group_id);

    public function getAllForSubject($subject_ids);

    public function getAllForStudentDirection($direction_id);

    public function getAllForSchoolYearAndSchool($company_year_id, $center_id);

    public function getAllSessionAttendance($company_year_id, $center_id, $session_id);

    public function getAllSessionAttendanceExport($center_id, $session_id);

    public function getAllSessionAttendanceAll($company_year_id, $session_id);

    public function getAllForSchoolYearAndSchoolAll($company_year_id);

    public function getAllForSchool($company_id);

    public function getAllCompanyActive($company_id);

    public function getAllCompanyInActive($company_id);

    public function getAllForKpiResponsibilities($company_id);

    public function getAllForSchoolAndGlobal($company_id);

    public function getAllForSchoolChat($company_id);

    public function getAllForPayroll($company_id);

    public function getAllExport($request);

    public function getAllAdmittedForSchool($company_year_id, $semester_id, $company_id);

    public function getAllAdmittedForSchoolMale($company_year_id, $semester_id, $company_id);

    public function getAllAdmittedForSchoolFemale($company_year_id, $semester_id, $company_id);

    public function getAllAdmittedForSchoolFilter($request);

    public function getAllAdmittedForSchoolExport($request);

    public function getAllActive($company_year_id, $semester_id, $company_id);

    public function getAllActiveForImprovement($company_year_id, $company_id);

    public function getAllPendingApproval($company_year_id, $semester_id, $company_id);

    public function getAllActiveExport($request);

    public function getAllRegistrationExport($request);

    public function getAllRegistration($company_year_id, $semester_id, $company_id);

    public function getAllFilter($request);

    public function getAllActiveFilter($request);

    public function getAllRegistrationFilter($request);

    public function getAllDeferred($company_year_id, $semester_id, $company_id);

    public function getAllDeferredExport($request);

    public function getAllDeferredMale($company_year_id, $semester_id, $company_id);

    public function getAllDeferredFemale($company_year_id, $semester_id, $company_id);

    public function getAllDeferredFilter($request);

    public function getAllDrop($company_year_id, $semester_id, $company_id);

    public function getAllDropMale($company_year_id, $semester_id, $company_id);

    public function getAllDropFemale($company_year_id, $semester_id, $company_id);

    public function getAllDropFilter($request);

    public function getAllAlumni($company_year_id, $semester_id, $company_id);

    public function getAllAlumniExport($request);

    public function getAllAlumniMale($company_year_id, $semester_id, $company_id);

    public function getAllAlumniFemale($company_year_id, $semester_id, $company_id);

    public function getAllAlumniFilter($request);

    public function getAllGraduating($company_year_id, $semester_id, $company_id);

    public function getAllGraduatingMale($company_year_id, $semester_id, $company_id);

    public function getAllGraduatingFemale($company_year_id, $semester_id, $company_id);

    public function getAllGraduatingFilter($request);

    public function getAllForSchoolYearSchoolAndSection($company_year_id, $company_id, $section_id);

    public function getSchoolForStudent($student_user_id, $company_year_id);

    public function create(array $data, $activate = true);

    public function erpSync(array $data, $activate = true);

    public function getAllForSection($section_id);

    public function getAllForSection2($section_id);

    public function getAllForDirection($section_id);

    public function getAllForSectionCurrency($section_id, $currency_id);

    public function getAllForDirectionCurrency($direction_id, $currency_id);

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids);

    public function getCountStudentsForSchoolAndSchoolYear($company_id, $schoolYearId);
}
