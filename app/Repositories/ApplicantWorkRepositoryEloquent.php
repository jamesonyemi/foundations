<?php

namespace App\Repositories;

use App\Models\Applicant_work;

class ApplicantWorkRepositoryEloquent implements ApplicantWorkRepository
{
    /**
     * @var Applicant_work
     */
    private $model;

    /**
     * ApplicantWorkRepositoryEloquent constructor.
     * @param Applicant_work $model
     */
    public function __construct(Applicant_work $model)
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
