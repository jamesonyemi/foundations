<?php

namespace App\Repositories;

use App\Models\ApplyingLeave;

class ApplyingLeaveRepositoryEloquent implements ApplyingLeaveRepository
{
    /**
     * @var ApplyingLeave
     */
    private $model;

    /**
     * ApplyingLeaveRepositoryEloquent constructor.
     * @param ApplyingLeave $model
     */
    public function __construct(ApplyingLeave $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForStudentAndSchoolYear($student_id, $company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('student_id', $student_id);
    }
}
