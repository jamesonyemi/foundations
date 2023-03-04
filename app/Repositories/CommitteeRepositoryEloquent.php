<?php

namespace App\Repositories;

use App\Models\Committee;

class CommitteeRepositoryEloquent implements CommitteeRepository
{
    /**
     * @var Committee
     */
    private $model;

    /**
     * LevelRepositoryEloquent constructor.
     * @param Committee $model
     */
    public function __construct(Committee $model)
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

    public function getAllForSection($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }
}
