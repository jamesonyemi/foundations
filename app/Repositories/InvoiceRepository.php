<?php

namespace App\Repositories;

interface InvoiceRepository
{
    public function getAll();

    public function getAllStudentsForSchool($company_id);

    public function getAllForSchoolYearSemester($company_id, $school_Year_id, $school_semester_id);

    public function getAllDebtor();

    public function getAllDebtorStudentsForSchool($company_id);

    public function getAllFullPaymentForSchoolAndSchoolYear($company_id, $company_year_id);

    public function getAllPartPaymentForSchoolAndSchoolYear($company_id, $company_year_id);

    public function getAllNoPaymentForSchoolAndSchoolYear($company_id, $company_year_id);
}
