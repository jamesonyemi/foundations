<?php

namespace App\Repositories;

use App\Models\MarkType;

class MarkTypeRepositoryEloquent implements MarkTypeRepository
{
    /**
     * @var MarkType
     */
    private $model;

    /**
     * MarkTypeRepositoryEloquent constructor.
     * @param MarkType $model
     */
    public function __construct(MarkType $model)
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
