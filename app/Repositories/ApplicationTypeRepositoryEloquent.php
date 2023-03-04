<?php

namespace App\Repositories;

use App\Models\ApplicationType;

class ApplicationTypeRepositoryEloquent implements ApplicationTypeRepository
{
    /**
     * @var ApplicationType
     */
    private $model;

    /**
     * LevelRepositoryEloquent constructor.
     * @param Level $model
     */
    public function __construct(ApplicationType $model)
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
