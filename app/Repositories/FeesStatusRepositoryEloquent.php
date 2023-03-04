<?php

namespace App\Repositories;

use App\Models\FeesStatus;
use Illuminate\Support\Facades\DB;

class FeesStatusRepositoryEloquent implements FeesStatusRepository
{
    /**
     * @var FeesStatus
     */
    private $model;

    /**
     * InvoiceRepositoryEloquent constructor.
     * @param FeesStatus $model
     */
    public function __construct(FeesStatus $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllStudentsForSchool($company_id)
    {
        return $this->model->join('students', 'students.user_id', '=', 'fees_status.user_id')
                           ->where('company_id', $company_id)
                           ->select('fees_status.*');
    }

    public function getAllDebtor()
    {
        return $this->model->where('paid', 0)
            ->select('*', DB::raw('sum(amount) as amount'))
            ->groupBy('user_id');
    }

    public function getAllDebtorStudentsForSchool($company_id)
    {
        return $this->model->join('students', 'students.user_id', '=', 'fees_status.user_id')
                           ->where('company_id', $company_id)
                            ->where('paid', 0)
                           ->select('*', DB::raw('sum(amount) as amount'))
                           ->groupBy('fees_status.user_id');
    }
}
