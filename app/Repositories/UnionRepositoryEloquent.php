<?php

namespace App\Repositories;

use App\Models\Union;

class UnionRepositoryEloquent implements UnionRepository
{
    /**
     * @var Direction
     */
    private $model;

    /**
     * DirectionRepositoryEloquent constructor.
     * @param Direction $model
     */
    public function __construct(Union $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id)
                    ->where('title', '!=', 'General Programme');
    }
}
