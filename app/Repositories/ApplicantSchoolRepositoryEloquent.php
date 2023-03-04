<?php

namespace App\Repositories;

use App\Models\Applicant_school;

class ApplicantSchoolRepositoryEloquent implements ApplicantSchoolRepository
{
    /**
     * @var Applicant_school
     */
    private $model;

    /**
     * ApplicantSchoolRepositoryEloquent constructor.
     * @param Applicant_school $model
     */
    public function __construct(Applicant_school $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForApplicant($user_id)
    {
        return $this->model->where('user_id', $user_id);
    }
}
