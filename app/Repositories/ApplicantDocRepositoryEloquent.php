<?php

namespace App\Repositories;

use App\Models\Applicant_doc;

class ApplicantDocRepositoryEloquent implements ApplicantDocRepository
{
    /**
     * @var Applicant_doc
     */
    private $model;

    /**
     * ApplicantWorkRepositoryEloquent constructor.
     * @param Applicant_doc $model
     */
    public function __construct(Applicant_doc $model)
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
