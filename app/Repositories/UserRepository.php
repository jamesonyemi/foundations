<?php

namespace App\Repositories;

interface UserRepository
{
    public function getAll();

    public function getUsersForRole($role);

    public function getParentsAndStudents();

    public function getAllUsersFromSchool($company_id, $company_year_id);

    public function getAllAdminAndTeachersForSchool($company_id);

    public function getAllStudentsParentsUsersFromSchool($company_id, $company_year_id, $student_group_id);

    public function getAllStudentsAndTeachersForSchoolSchoolYearAndSection($company_id, $company_year_id, $student_section_id);
}
