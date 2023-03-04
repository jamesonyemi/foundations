<?php

namespace App\Repositories;

use App\Models\MarkSystem;

class MarkSystemRepositoryEloquent implements MarkSystemRepository
{
    /**
     * @var MarkSystem
     */
    private $model;

    /**
     * MarkSystemRepositoryEloquent constructor.
     * @param MarkSystem $model
     */
    public function __construct(MarkSystem $model)
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

    public function getAllForSchoolSubject($company_id, $subject_id)
    {
        return $this->model->where('company_id', $company_id)
            ->where('subject_id', $subject_id);
    }
}
