<?php

namespace App\Repositories;

use App\Models\MealType;

class MealTypeRepositoryEloquent implements MealTypeRepository
{
    /**
     * @var MealType
     */
    private $model;

    /**
     * MealTypeRepositoryEloquent constructor.
     * @param MealType $model
     */
    public function __construct(MealType $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
