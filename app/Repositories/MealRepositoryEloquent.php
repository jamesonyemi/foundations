<?php

namespace App\Repositories;

use App\Models\Meal;

class MealRepositoryEloquent implements MealRepository
{
    /**
     * @var Meal
     */
    private $model;

    /**
     * MealRepositoryEloquent constructor.
     * @param Meal $model
     */
    public function __construct(Meal $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForDate($date)
    {
        return $this->model->where('serve_start_date', '<=', $date)
                            ->where('serve_end_date', '>=', $date);
    }
}
