<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepositoryEloquent implements PaymentRepository
{
    /**
     * @var Payment
     */
    private $model;

    /**
     * PaymentRepositoryEloquent constructor.
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllStudentsForSchool($company_id, $year_id, $semester_id)
    {
        return $this->model->join('students', 'students.user_id', '=', 'payments.user_id')
                           ->where('company_id', $company_id)
                           ->where('payments.company_year_id', $year_id)
                           ->where('payments.semester_id', $semester_id)
                           ->select('payments.*');
    }
}
