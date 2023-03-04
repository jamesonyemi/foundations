<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceRepositoryEloquent implements InvoiceRepository
{
    /**
     * @var Invoice
     */
    private $model;

    /**
     * InvoiceRepositoryEloquent constructor.
     * @param Invoice $model
     */
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllStudentsForSchool($company_id)
    {
        return $this->model->join('students', 'students.user_id', '=', 'invoices.user_id')
                           ->where('students.company_id', $company_id)
                           ->select('invoices.*');
    }

    public function getAllForSchoolYearSemester($company_id, $school_Year_id, $school_semester_id)
    {
        return $this->model->join('students', 'students.user_id', '=', 'invoices.user_id')
            ->where('students.company_id', $company_id)
            ->where('invoices.company_id', $company_id)
            ->where('invoices.company_year_id', $school_Year_id)
            ->where('invoices.semester_id', $school_semester_id)
            ->select('invoices.*');
    }

    public function getAllDebtor()
    {
        return $this->model->where('paid', 0)
            ->select('*', DB::raw('sum(amount) as amount'))
            ->groupBy('user_id');
    }

    public function getAllDebtorStudentsForSchool($company_id)
    {
        return $this->model->join('students', 'students.user_id', '=', 'invoices.user_id')
                           ->where('students.company_id', $company_id)
                            ->where('paid', 0)
                           ->select('*', DB::raw('sum(amount) as amount'))
                           ->groupBy('invoices.user_id');
    }

    public function getAllFullPaymentForSchoolAndSchoolYear($company_id, $company_year_id)
    {
        return $this->model->where('company_id', $company_id)
            ->where('company_year_id', $company_year_id)
            ->where('paid', 1);
    }

    public function getAllPartPaymentForSchoolAndSchoolYear($company_id, $company_year_id)
    {
        return $this->model->where('company_id', $company_id)
            ->where('company_year_id', $company_year_id)
            ->where('paid', 0)
            ->where('paid_total', '>', 0);
    }

    public function getAllNoPaymentForSchoolAndSchoolYear($company_id, $company_year_id)
    {
        return $this->model->where('company_id', $company_id)
            ->where('company_year_id', $company_year_id)
            ->where('paid_total', 0);
    }
}
