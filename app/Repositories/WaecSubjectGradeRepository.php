<?php

namespace App\Repositories;

use App\Models\WaecSubjectGrade;

class WaecSubjectGradeRepository
{
    /**
     * @var WaecExamRepository
     */
    private $model;

    /**
     * WaecExamRepository constructor.
     * @param WaecExam $model
     */
    public function __construct(WaecSubjectGrade $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }
}
