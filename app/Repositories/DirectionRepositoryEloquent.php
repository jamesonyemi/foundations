<?php

namespace App\Repositories;

use App\Models\Direction;

class DirectionRepositoryEloquent implements DirectionRepository
{
    /**
     * @var Direction
     */
    private $model;

    /**
     * DirectionRepositoryEloquent constructor.
     * @param Direction $model
     */
    public function __construct(Direction $model)
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

    public function getAllForSection($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }
}
